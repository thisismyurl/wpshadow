<?php
/**
 * Wpbakery Page Builder Custom Elements Diagnostic
 *
 * Wpbakery Page Builder Custom Elements issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.827.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpbakery Page Builder Custom Elements Diagnostic Class
 *
 * @since 1.827.0000
 */
class Diagnostic_WpbakeryPageBuilderCustomElements extends Diagnostic_Base {

	protected static $slug = 'wpbakery-page-builder-custom-elements';
	protected static $title = 'Wpbakery Page Builder Custom Elements';
	protected static $description = 'Wpbakery Page Builder Custom Elements issues found';
	protected static $family = 'functionality';

	public static function check() {
		// Check if WPBakery is installed
		if ( ! defined( 'WPB_VC_VERSION' ) && ! function_exists( 'vc_map' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check custom element registration
		$custom_elements = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			 WHERE option_name LIKE 'wpb_js_custom_element_%'"
		);

		if ( $custom_elements === 0 ) {
			$issues[] = 'no_custom_elements_registered';
			$threat_level += 15;
		} elseif ( $custom_elements > 100 ) {
			$issues[] = 'excessive_custom_elements';
			$threat_level += 20;
		}

		// Check template library
		$templates = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'vc_grid_item'
			)
		);
		if ( $templates > 50 ) {
			$issues[] = 'excessive_template_library';
			$threat_level += 15;
		}

		// Check custom CSS injection
		$custom_css = get_option( 'wpb_js_custom_css', '' );
		if ( ! empty( $custom_css ) && strlen( $custom_css ) > 50000 ) {
			$issues[] = 'excessive_custom_css';
			$threat_level += 15;
		}

		// Check asset optimization
		$disable_frontend_css = get_option( 'wpb_js_content_types_custom_css_js', array() );
		if ( empty( $disable_frontend_css ) ) {
			$issues[] = 'frontend_assets_not_optimized';
			$threat_level += 15;
		}

		// Check for inline JS security
		$posts_with_inline_js = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			 WHERE meta_key = '_wpb_shortcodes_custom_css' 
			 AND meta_value LIKE '%<script%'"
		);
		if ( $posts_with_inline_js > 0 ) {
			$issues[] = 'inline_javascript_detected';
			$threat_level += 25;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of custom element issues */
				__( 'WPBakery custom elements have issues: %s. This affects performance and security of page builder content.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpbakery-page-builder-custom-elements',
			);
		}
		
		return null;
	}
}
