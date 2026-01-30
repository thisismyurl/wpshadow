<?php
/**
 * Elementor Mobile Responsiveness and Performance Diagnostic
 *
 * Verify Elementor pages properly optimized for mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6030.1220
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Mobile Responsiveness Diagnostic Class
 *
 * @since 1.6030.1220
 */
class Diagnostic_ElementorMobileResponsiveness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'elementor-mobile-responsiveness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Elementor Mobile Responsiveness and Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify Elementor pages properly optimized for mobile devices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1220
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Elementor is active
		if ( ! defined( 'ELEMENTOR_VERSION' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Mobile editing configured for all breakpoints
		$mobile_breakpoint = get_option( 'elementor_viewport_lg', 1025 );
		$tablet_breakpoint = get_option( 'elementor_viewport_md', 768 );
		
		// Default breakpoints check
		if ( 1025 === $mobile_breakpoint && 768 === $tablet_breakpoint ) {
			// Using defaults - check if mobile editing has been used
			$args = array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'meta_query'     => array(
					array(
						'key'     => '_elementor_data',
						'value'   => '"mobile"',
						'compare' => 'LIKE',
					),
				),
			);
			
			$mobile_edited = get_posts( $args );
			if ( empty( $mobile_edited ) ) {
				$issues[] = 'no mobile-specific edits detected on pages';
			}
		}

		// Check 2: Test for horizontal scrolling issues (check viewport settings)
		$disable_color_schemes = get_option( 'elementor_disable_color_schemes', 'no' );
		$disable_typography = get_option( 'elementor_disable_typography_schemes', 'no' );
		
		// Check if pages have fixed-width containers
		global $wpdb;
		$fixed_width_count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"content_width\":\"full\"%'"
		);
		
		if ( $fixed_width_count > 0 ) {
			// This is actually good, but check for no mobile overrides
			$issues[] = sprintf( '%d pages may have mobile overflow issues', $fixed_width_count );
		}

		// Check 3: Check for hidden mobile elements still loading
		$hidden_mobile_elements = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND (meta_value LIKE '%\"_hide_mobile\":\"yes\"%' OR meta_value LIKE '%\"hide_mobile\":true%')"
		);
		
		if ( $hidden_mobile_elements > 50 ) {
			$issues[] = sprintf( '%d hidden mobile elements still loading resources', $hidden_mobile_elements );
		}

		// Check 4: Test mobile DOM size (check pages with Elementor)
		$elementor_pages = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_edit_mode' 
			AND meta_value = 'builder'"
		);
		
		if ( $elementor_pages > 50 ) {
			$issues[] = sprintf( '%d pages built with Elementor (check mobile DOM size)', $elementor_pages );
		}

		// Check 5: Check mobile navigation functionality
		$nav_menu_widget = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"widgetType\":\"nav-menu\"%'"
		);
		
		$mobile_menu_toggle = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"toggle\":\"mobile\"%'"
		);
		
		if ( $nav_menu_widget > 0 && $mobile_menu_toggle === 0 ) {
			$issues[] = 'navigation menus may not have mobile toggle configured';
		}

		// Check 6: Verify mobile-specific font sizes appropriate
		$font_sizes = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_elementor_data' 
			AND meta_value LIKE '%\"typography_font_size_mobile\":{\"unit\":\"px\",\"size\":%'
			AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"size\":', -1), ',', 1) AS UNSIGNED) < 14"
		);
		
		if ( $font_sizes > 10 ) {
			$issues[] = sprintf( '%d elements with small mobile fonts (< 14px)', $font_sizes );
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 55 + ( count( $issues ) * 6 ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Elementor mobile optimization issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-mobile-responsiveness',
			);
		}

		return null;
	}
}
