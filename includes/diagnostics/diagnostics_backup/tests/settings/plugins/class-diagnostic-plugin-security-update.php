<?php
/**
 * Plugin Security Update Available Diagnostic
 *
 * Detects plugins with critical security patches available.
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
 * Plugin Security Update Class
 *
 * Identifies plugins with available security patches.
 * Critical for preventing exploitation of known vulnerabilities.
 *
 * @since 1.5029.1700
 */
class Diagnostic_Plugin_Security_Update extends Diagnostic_Base {

	protected static $slug        = 'plugin-security-update';
	protected static $title       = 'Plugin Security Update Available';
	protected static $description = 'Detects plugins with security patches';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_plugin_security_updates';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Trigger update check.
		wp_update_plugins();

		$update_plugins = get_site_transient( 'update_plugins' );
		
		if ( empty( $update_plugins ) || empty( $update_plugins->response ) ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$security_updates = array();

		foreach ( $update_plugins->response as $plugin_path => $plugin_data ) {
			// Check if update mentions security in changelog.
			if ( isset( $plugin_data->package ) ) {
				$slug = dirname( $plugin_path );
				$api_url = "https://api.wordpress.org/plugins/info/1.0/{$slug}.json";
				$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

				if ( ! is_wp_error( $response ) ) {
					$body = wp_remote_retrieve_body( $response );
					$data = json_decode( $body, true );

					if ( isset( $data['sections']['changelog'] ) ) {
						$changelog = $data['sections']['changelog'];
						
						// Check for security keywords.
						if ( preg_match( '/(security|vulnerability|xss|sql injection|exploit|patch|fix|cve)/i', $changelog ) ) {
							$security_updates[] = array(
								'name' => $data['name'] ?? dirname( $plugin_path ),
								'slug' => $slug,
								'current_version' => $plugin_data->Version ?? 'Unknown',
								'new_version' => $plugin_data->new_version ?? 'Unknown',
								'security_note' => 'Security fix mentioned in changelog',
							);
						}
					}
				}
			}

			// Limit API calls.
			if ( count( $security_updates ) >= 10 ) {
				break;
			}
		}

		if ( ! empty( $security_updates ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of plugins */
					__( '%d plugins have security updates available. Update immediately!', 'wpshadow' ),
					count( $security_updates )
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-plugin-updates',
				'data'         => array(
					'security_updates' => $security_updates,
					'total_updates' => count( $security_updates ),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
		return null;
	}
}
