<?php
/**
 * Visual Composer Premium Elements Diagnostic
 *
 * Visual Composer Premium Elements issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.834.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Composer Premium Elements Diagnostic Class
 *
 * @since 1.834.0000
 */
class Diagnostic_VisualComposerPremiumElements extends Diagnostic_Base {

	protected static $slug = 'visual-composer-premium-elements';
	protected static $title = 'Visual Composer Premium Elements';
	protected static $description = 'Visual Composer Premium Elements issues found';
	protected static $family = 'functionality';

	public static function check() {
		// Check if Visual Composer is installed
		if ( ! defined( 'WPB_VC_VERSION' ) && ! class_exists( 'Vc_Manager' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check license activation
		$vc_license = get_option( 'wpb_js_composer_purchase_code', '' );
		if ( empty( $vc_license ) ) {
			$issues[] = 'license_not_activated';
			$threat_level += 25;
		}

		// Check for updates
		$update_data = get_site_transient( 'update_plugins' );
		$vc_needs_update = false;
		if ( $update_data && isset( $update_data->response ) ) {
			foreach ( $update_data->response as $plugin => $data ) {
				if ( strpos( $plugin, 'js_composer' ) !== false ) {
					$vc_needs_update = true;
					break;
				}
			}
		}
		if ( $vc_needs_update ) {
			$issues[] = 'premium_elements_update_available';
			$threat_level += 20;
		}

		// Check custom element count
		global $wpdb;
		$custom_elements = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			 WHERE option_name LIKE '_wpb_custom_element_%'"
		);
		if ( $custom_elements > 50 ) {
			$issues[] = 'excessive_custom_elements';
			$threat_level += 15;
		}

		// Check element performance
		$disable_frontend_editor = get_option( 'wpb_js_use_custom', 'on' );
		if ( $disable_frontend_editor === 'off' ) {
			$issues[] = 'frontend_editor_always_loaded';
			$threat_level += 15;
		}

		// Check for deprecated elements
		$deprecated_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			 WHERE meta_key = '_wpb_vc_js_status' 
			 AND meta_value LIKE '%deprecated%'"
		);
		if ( $deprecated_count > 0 ) {
			$issues[] = 'using_deprecated_elements';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of premium element issues */
				__( 'Visual Composer premium elements have issues: %s. This affects functionality and security of premium features.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/visual-composer-premium-elements',
			);
		}
		
		return null;
	}
}
