<?php
/**
 * Jupiter Theme Elementor Templates Diagnostic
 *
 * Jupiter Theme Elementor Templates needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1334.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jupiter Theme Elementor Templates Diagnostic Class
 *
 * @since 1.1334.0000
 */
class Diagnostic_JupiterThemeElementorTemplates extends Diagnostic_Base {

	protected static $slug = 'jupiter-theme-elementor-templates';
	protected static $title = 'Jupiter Theme Elementor Templates';
	protected static $description = 'Jupiter Theme Elementor Templates needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check if Jupiter theme and Elementor are active
		$theme = wp_get_theme();
		if ( $theme->get( 'Name' ) !== 'Jupiter' && $theme->get_template() !== 'jupiter' ) {
			return null;
		}
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check template library size
		$templates = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type = %s",
				'elementor_library'
			)
		);
		if ( $templates > 100 ) {
			$issues[] = 'excessive_template_library';
			$threat_level += 20;
		}

		// Check cache configuration
		$elementor_cache = get_option( 'elementor_disable_color_schemes', 'no' );
		if ( $elementor_cache === 'no' ) {
			$issues[] = 'css_cache_not_optimized';
			$threat_level += 15;
		}

		// Check custom widgets
		$custom_widgets = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			 WHERE option_name LIKE 'mk_custom_elementor_widget_%'"
		);
		if ( $custom_widgets > 50 ) {
			$issues[] = 'excessive_custom_widgets';
			$threat_level += 15;
		}

		// Check CSS file generation
		$upload_dir = wp_upload_dir();
		$css_dir = $upload_dir['basedir'] . '/elementor/css';
		if ( is_dir( $css_dir ) ) {
			$css_files = glob( $css_dir . '/*.css' );
			if ( $css_files && count( $css_files ) > 500 ) {
				$issues[] = 'excessive_css_files';
				$threat_level += 20;
			}
		}

		// Check template conflicts
		$conflict_templates = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} 
				 WHERE meta_key = %s 
				 AND meta_value LIKE %s",
				'_elementor_template_type',
				'%error%'
			)
		);
		if ( $conflict_templates > 0 ) {
			$issues[] = 'template_conflicts_detected';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of template issues */
				__( 'Jupiter theme Elementor templates have issues: %s. Total templates: %d. This slows page editing and increases database load.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) ),
				$templates
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/jupiter-theme-elementor-templates',
			);
		}
		
		return null;
	}
}
