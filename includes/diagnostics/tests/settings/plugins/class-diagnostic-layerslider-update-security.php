<?php
/**
 * LayerSlider Update Security Diagnostic
 *
 * LayerSlider updates not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.285.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LayerSlider Update Security Diagnostic Class
 *
 * @since 1.285.0000
 */
class Diagnostic_LayersliderUpdateSecurity extends Diagnostic_Base {

	protected static $slug = 'layerslider-update-security';
	protected static $title = 'LayerSlider Update Security';
	protected static $description = 'LayerSlider updates not configured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'LS_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check license activation
		$license = get_option( 'layerslider-authorized-site', '' );
		if ( empty( $license ) ) {
			$issues[] = 'license_not_activated';
			$threat_level += 20;
		}

		// Check plugin version
		$current_version = defined( 'LS_PLUGIN_VERSION' ) ? LS_PLUGIN_VERSION : '';
		$update_data = get_site_transient( 'update_plugins' );
		$has_update = false;
		if ( $update_data && isset( $update_data->response ) ) {
			foreach ( $update_data->response as $plugin => $data ) {
				if ( strpos( $plugin, 'layerslider' ) !== false ) {
					$has_update = true;
					break;
				}
			}
		}
		if ( $has_update ) {
			$issues[] = 'update_available';
			$threat_level += 25;
		}

		// Check for known vulnerabilities (example versions)
		if ( version_compare( $current_version, '7.9.0', '<' ) ) {
			$issues[] = 'vulnerable_version';
			$threat_level += 30;
		}

		// Check automatic updates
		$auto_update = get_option( 'layerslider_auto_update', false );
		if ( ! $auto_update ) {
			$issues[] = 'auto_update_disabled';
			$threat_level += 15;
		}

		// Check update channel
		$update_channel = get_option( 'layerslider_update_channel', 'stable' );
		if ( $update_channel !== 'stable' ) {
			$issues[] = 'using_beta_channel';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of update security issues */
				__( 'LayerSlider update security has problems: %s. This exposes your site to known vulnerabilities and security exploits.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/layerslider-update-security',
			);
		}
		
		return null;
	}
}
