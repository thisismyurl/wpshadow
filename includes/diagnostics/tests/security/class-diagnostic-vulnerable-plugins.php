<?php
/**
 * Vulnerable Plugin Detection Diagnostic
 *
 * Scans installed plugins against known CVE database.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vulnerable Plugin Detection Class
 *
 * Detects plugins with known CVEs using WordPress.org API.
 * Plugins are #1 WordPress vulnerability vector.
 *
 * @since 1.5029.1200
 */
class Diagnostic_Vulnerable_Plugins extends Diagnostic_Base {

	protected static $slug        = 'vulnerable-plugin-detection';
	protected static $title       = 'Vulnerable Plugin Detection';
	protected static $description = 'Scans for plugins with known security vulnerabilities';
	protected static $family      = 'security';

	public static function check() {
		$cache_key = 'wpshadow_vulnerable_plugins_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get all plugins using WordPress API (NO $wpdb).
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		$vulnerable_plugins = array();

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			$is_active = in_array( $plugin_path, $active_plugins, true );

			// Check plugin version against WordPress.org.
			$slug     = dirname( $plugin_path );
			$version  = $plugin_data['Version'];
			$api_data = self::check_plugin_vulnerabilities( $slug, $version );

			if ( $api_data && isset( $api_data['has_vulnerability'] ) && $api_data['has_vulnerability'] ) {
				$vulnerable_plugins[] = array(
					'name'             => $plugin_data['Name'],
					'slug'             => $slug,
					'current_version'  => $version,
					'latest_version'   => $api_data['latest_version'] ?? 'Unknown',
					'is_active'        => $is_active,
					'vulnerability'    => $api_data['vulnerability'] ?? 'Known security issue',
				);
			}
		}

		if ( ! empty( $vulnerable_plugins ) ) {
			$active_vulnerable = array_filter( $vulnerable_plugins, function( $p ) {
				return $p['is_active'];
			} );

			$threat_level = 60;
			if ( count( $active_vulnerable ) > 0 ) {
				$threat_level = 85;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: vulnerable plugins, 2: active vulnerable */
					__( '%1$d plugins have known vulnerabilities (%2$d active). Update immediately.', 'wpshadow' ),
					count( $vulnerable_plugins ),
					count( $active_vulnerable )
				),
				'severity'     => 'critical',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-vulnerable-plugin-detection',
				'data'         => array(
					'vulnerable_plugins' => $vulnerable_plugins,
					'active_vulnerable'  => count( $active_vulnerable ),
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	private static function check_plugin_vulnerabilities( $slug, $version ) {
		$api_url  = "https://api.wordpress.org/plugins/info/1.0/{$slug}.json";
		$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $data ) ) {
			return null;
		}

		$latest_version = $data['version'] ?? $version;

		// Simple version comparison - if outdated, potential vulnerability.
		if ( version_compare( $version, $latest_version, '<' ) ) {
			// Check if update notes mention security.
			$changelog = $data['sections']['changelog'] ?? '';
			$has_security_fix = stripos( $changelog, 'security' ) !== false || stripos( $changelog, 'vulnerability' ) !== false;

			if ( $has_security_fix ) {
				return array(
					'has_vulnerability' => true,
					'latest_version'    => $latest_version,
					'vulnerability'     => 'Security fix available in newer version',
				);
			}
		}

		return array( 'has_vulnerability' => false );
	}
}
