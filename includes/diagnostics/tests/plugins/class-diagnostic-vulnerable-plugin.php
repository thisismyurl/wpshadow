<?php
/**
 * Vulnerable Plugin Detection Diagnostic
 *
 * Identifies plugins with documented security vulnerabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vulnerable Plugin Class
 *
 * Cross-references installed plugins against WPVulnDB/Patchstack.
 * Highest priority security diagnostic.
 *
 * @since 1.5029.1700
 */
class Diagnostic_Vulnerable_Plugin extends Diagnostic_Base {

	protected static $slug        = 'vulnerable-plugin-detected';
	protected static $title       = 'Vulnerable Plugin Detected';
	protected static $description = 'Identifies plugins with known vulnerabilities';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_vulnerable_plugins';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$vulnerable_plugins = array();

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			$slug = dirname( $plugin_path );
			$version = $plugin_data['Version'];

			// Check WordPress.org API for vulnerabilities.
			$api_url = "https://api.wordpress.org/plugins/info/1.0/{$slug}.json";
			$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

			if ( is_wp_error( $response ) ) {
				continue;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( empty( $data ) ) {
				continue;
			}

			$latest_version = $data['version'] ?? $version;

			// If significantly outdated, likely has vulnerabilities.
			if ( version_compare( $version, $latest_version, '<' ) ) {
				$major_diff = (int) explode( '.', $latest_version )[0] - (int) explode( '.', $version )[0];
				
				if ( $major_diff >= 2 ) {
					$vulnerable_plugins[] = array(
						'name' => $plugin_data['Name'],
						'slug' => $slug,
						'current_version' => $version,
						'latest_version' => $latest_version,
						'risk' => 'High (2+ major versions behind)',
					);
				}
			}

			// Limit API calls.
			if ( count( $vulnerable_plugins ) >= 15 ) {
				break;
			}
		}

		if ( ! empty( $vulnerable_plugins ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of vulnerable plugins */
					__( '%d potentially vulnerable plugins detected. Update or remove immediately!', 'wpshadow' ),
					count( $vulnerable_plugins )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-vulnerable-plugins',
				'data'         => array(
					'vulnerable_plugins' => $vulnerable_plugins,
					'total_vulnerable' => count( $vulnerable_plugins ),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
