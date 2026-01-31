<?php
/**
 * Plugin API Rate Limiting Diagnostic
 *
 * Detects plugins making excessive external API calls.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5030.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin API Rate Limiting Class
 *
 * Monitors and detects excessive external API requests.
 *
 * @since 1.5030.1045
 */
class Diagnostic_Plugin_API_Rate_Limiting extends Diagnostic_Base {

	protected static $slug        = 'plugin-api-rate-limiting';
	protected static $title       = 'Plugin API Rate Limiting';
	protected static $description = 'Detects excessive external API calls';
	protected static $family      = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5030.1045
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_api_rate_limiting';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Track HTTP requests during this diagnostic.
		$api_calls = array();
		
		add_filter( 'pre_http_request', function( $preempt, $args, $url ) use ( &$api_calls ) {
			// Track the request.
			$host = wp_parse_url( $url, PHP_URL_HOST );
			
			if ( ! isset( $api_calls[ $host ] ) ) {
				$api_calls[ $host ] = 0;
			}
			$api_calls[ $host ]++;
			
			return $preempt;
		}, 10, 3 );

		// Check if plugins make API calls on every page load.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$excessive_apis = array();

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			if ( ! in_array( $plugin_path, $active_plugins, true ) ) {
				continue;
			}

			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_path );
			$api_usage  = $this->scan_for_api_calls( $plugin_dir );

			if ( $api_usage['calls'] > 5 ) {
				$excessive_apis[] = array(
					'name'       => $plugin_data['Name'],
					'slug'       => dirname( $plugin_path ),
					'api_calls'  => $api_usage['calls'],
					'endpoints'  => $api_usage['endpoints'],
					'risk_level' => $api_usage['calls'] > 10 ? 'high' : 'medium',
				);
			}

			// Limit scans.
			if ( count( $excessive_apis ) >= 10 ) {
				break;
			}
		}

		if ( ! empty( $excessive_apis ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of plugins */
					__( '%d plugins make excessive API calls. Consider caching or rate limiting.', 'wpshadow' ),
					count( $excessive_apis )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-api-rate-limiting',
				'data'         => array(
					'plugins_with_excessive_apis' => $excessive_apis,
					'total_flagged'               => count( $excessive_apis ),
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Scan plugin for API call patterns.
	 *
	 * @since  1.5030.1045
	 * @param  string $plugin_dir Plugin directory.
	 * @return array  API usage statistics.
	 */
	private static function scan_for_api_calls( $plugin_dir ) {
		$calls     = 0;
		$endpoints = array();
		$php_files = glob( $plugin_dir . '/*.php' );
		
		if ( ! empty( $php_files ) ) {
			$php_files = array_merge( $php_files, glob( $plugin_dir . '/**/*.php' ) );
		}

		// Limit to 15 files.
		$php_files = array_slice( $php_files, 0, 15 );

		foreach ( $php_files as $file ) {
			if ( ! is_readable( $file ) ) {
				continue;
			}

			$content = file_get_contents( $file );

			// Count wp_remote_* functions.
			preg_match_all( '/wp_remote_(?:get|post|request|head)\s*\(/i', $content, $matches );
			$calls += count( $matches[0] );

			// Extract API endpoints.
			if ( preg_match_all( '/[\'"]https?:\/\/([^\/\'"]+)/i', $content, $url_matches ) ) {
				foreach ( $url_matches[1] as $host ) {
					if ( ! in_array( $host, $endpoints, true ) ) {
						$endpoints[] = $host;
					}
				}
			}
		}

		return array(
			'calls'     => $calls,
			'endpoints' => array_slice( $endpoints, 0, 10 ),
		);
	}
}
