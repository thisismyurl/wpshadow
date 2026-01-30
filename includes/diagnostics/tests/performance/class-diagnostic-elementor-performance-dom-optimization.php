<?php
/**
 * Elementor Performance and DOM Size Optimization Diagnostic
 *
 * Verify Elementor pages optimized to prevent DOM size issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6030.1250
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Performance DOM Optimization Diagnostic Class
 *
 * @since 1.6030.1250
 */
class Diagnostic_ElementorPerformanceDomOptimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'elementor-performance-dom-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Elementor Performance and DOM Size Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify Elementor pages optimized to prevent DOM size issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1250
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Elementor is active
		if ( ! defined( 'ELEMENTOR_VERSION' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return null;
		}

		$issues = array();
		global $wpdb;

		// Check 1: Verify Elementor's CSS optimization enabled
		$css_print_method = get_option( 'elementor_css_print_method', 'external' );
		$optimized_dom_output = get_option( 'elementor_optimized_dom_output', 'disabled' );
		
		if ( 'internal' === $css_print_method ) {
			$issues[] = 'CSS inline loading (should use external for performance)';
		}
		
		if ( 'disabled' === $optimized_dom_output ) {
			$issues[] = 'optimized DOM output not enabled';
		}

		// Check 2: Test for excessive nested sections/containers
		$nested_sections = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND (
				meta_value LIKE '%\"elType\":\"section\"%' 
				OR meta_value LIKE '%\"elType\":\"container\"%'
			)"
		);
		
		if ( $nested_sections > 500 ) {
			$issues[] = sprintf( '%d nested sections/containers (excessive DOM nodes)', $nested_sections );
		}

		// Check 3: Check for unused Elementor widgets still loaded
		$widget_usage = array();
		$all_widgets = array( 'heading', 'image', 'text-editor', 'button', 'divider', 'spacer', 'google_maps', 'icon', 'image-box', 'icon-box', 'star-rating', 'image-carousel', 'image-gallery', 'icon-list', 'counter', 'progress', 'testimonial', 'tabs', 'accordion', 'toggle', 'social-icons', 'alert', 'audio', 'shortcode', 'html', 'menu-anchor', 'sidebar' );
		
		foreach ( $all_widgets as $widget ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) 
					FROM {$wpdb->postmeta} 
					WHERE meta_key = '_elementor_data' 
					AND meta_value LIKE %s",
					'%"widgetType":"' . $widget . '"%'
				)
			);
			
			if ( $count > 0 ) {
				$widget_usage[ $widget ] = $count;
			}
		}
		
		$used_widgets = count( $widget_usage );
		if ( $used_widgets > 15 ) {
			$issues[] = sprintf( '%d different widget types used (consider limiting)', $used_widgets );
		}

		// Check 4: Verify Elementor experiments enabled
		$experiments = get_option( 'elementor_experiment-container', 'inactive' );
		$flexbox_container = get_option( 'elementor_experiment-e_dom_optimization', 'inactive' );
		
		if ( 'inactive' === $experiments ) {
			$issues[] = 'Container experiment not enabled (improves DOM structure)';
		}
		
		if ( 'inactive' === $flexbox_container ) {
			$issues[] = 'DOM optimization experiment not enabled';
		}

		// Check 5: Check for lazy load enabled
		$lazy_load = get_option( 'elementor_lazy_load', 'no' );
		$lazy_load_background = get_option( 'elementor_lazy_load_background_images', 'no' );
		
		if ( 'no' === $lazy_load ) {
			$issues[] = 'lazy load not enabled for images/videos';
		}
		
		if ( 'no' === $lazy_load_background ) {
			$issues[] = 'lazy load not enabled for background images';
		}

		// Check 6: Verify Elementor CSS/JS minification
		$minify_css = get_option( 'elementor_css_minify', 'no' );
		
		if ( 'no' === $minify_css ) {
			$issues[] = 'CSS minification not enabled';
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 60 + ( count( $issues ) * 5 ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Elementor performance/DOM issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-performance-dom-optimization',
			);
		}

		return null;
	}
}
