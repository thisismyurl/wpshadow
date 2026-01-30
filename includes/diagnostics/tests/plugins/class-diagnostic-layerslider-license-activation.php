<?php
/**
 * LayerSlider License Activation Diagnostic
 *
 * LayerSlider license not activated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.284.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LayerSlider License Activation Diagnostic Class
 *
 * @since 1.284.0000
 */
class Diagnostic_LayersliderLicenseActivation extends Diagnostic_Base {

	protected static $slug = 'layerslider-license-activation';
	protected static $title = 'LayerSlider License Activation';
	protected static $description = 'LayerSlider license not activated';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'LS_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		// Check if LayerSlider is active
		if ( ! defined( 'LS_PLUGIN_VERSION' ) && ! class_exists( 'LS_Sliders' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check license key
		$license_key = get_option( 'layerslider-purchase-code', '' );
		if ( empty( $license_key ) ) {
			$issues[] = 'license_key_not_entered';
			$threat_level += 30;
		}

		// Check activation status
		$authorized_site = get_option( 'layerslider-authorized-site', '' );
		if ( empty( $authorized_site ) && ! empty( $license_key ) ) {
			$issues[] = 'license_not_activated';
			$threat_level += 25;
		}

		// Check update notifications
		$last_update_check = get_option( 'layerslider-last-update-check', 0 );
		if ( $last_update_check < strtotime( '-30 days' ) ) {
			$issues[] = 'update_check_outdated';
			$threat_level += 20;
		}

		// Check bundled vs standalone
		$bundled = get_option( 'layerslider_bundled', false );
		if ( $bundled && empty( $license_key ) ) {
			$issues[] = 'bundled_version_no_updates';
			$threat_level += 15;
		}

		// Check support access
		$support_access = get_option( 'layerslider-support-access', '' );
		if ( empty( $support_access ) && ! empty( $license_key ) ) {
			$issues[] = 'support_access_unavailable';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of license issues */
				__( 'LayerSlider license has activation problems: %s. This prevents updates and support access.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/layerslider-license-activation',
			);
		}
		
		return null;
	}
}
