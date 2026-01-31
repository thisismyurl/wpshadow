<?php
/**
 * Cloudflare Worker Scripts Diagnostic
 *
 * Cloudflare Worker Scripts needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.993.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudflare Worker Scripts Diagnostic Class
 *
 * @since 1.993.0000
 */
class Diagnostic_CloudflareWorkerScripts extends Diagnostic_Base {

	protected static $slug = 'cloudflare-worker-scripts';
	protected static $title = 'Cloudflare Worker Scripts';
	protected static $description = 'Cloudflare Worker Scripts needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'CLOUDFLARE_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check if Cloudflare API credentials are set
		$api_key = get_option( 'cloudflare_api_key', '' );
		$api_email = get_option( 'cloudflare_api_email', '' );
		if ( empty( $api_key ) || empty( $api_email ) ) {
			$issues[] = 'Cloudflare API credentials not configured';
		}

		// Check for worker script configuration
		$worker_enabled = get_option( 'cloudflare_worker_enabled', '0' );
		if ( '1' === $worker_enabled ) {
			$worker_script = get_option( 'cloudflare_worker_script', '' );
			if ( empty( $worker_script ) ) {
				$issues[] = 'worker scripts enabled but no script configured';
			}
		}

		// Check for worker route conflicts
		$worker_routes = get_option( 'cloudflare_worker_routes', array() );
		if ( ! empty( $worker_routes ) && is_array( $worker_routes ) ) {
			foreach ( $worker_routes as $route ) {
				if ( false !== strpos( $route, '/*' ) && false !== strpos( $route, '/wp-admin' ) ) {
					$issues[] = 'worker route conflicts with WordPress admin';
					break;
				}
			}
		}

		// Check for cached API responses
		$cache_ttl = get_option( 'cloudflare_worker_cache_ttl', 0 );
		if ( $cache_ttl > 3600 && is_user_logged_in() ) {
			$issues[] = 'worker cache TTL too high for authenticated users';
		}

		// Check for worker KV namespace configuration
		$kv_namespace = get_option( 'cloudflare_worker_kv_namespace', '' );
		if ( '1' === $worker_enabled && empty( $kv_namespace ) ) {
			$issues[] = 'worker KV namespace not configured for data storage';
		}

		// Check for SSL/TLS compatibility with workers
		$ssl_mode = get_option( 'cloudflare_ssl_mode', 'off' );
		if ( '1' === $worker_enabled && 'off' === $ssl_mode ) {
			$issues[] = 'SSL disabled but workers active (security risk)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Cloudflare Worker script configuration issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cloudflare-worker-scripts',
			);
		}

		return null;
	}
}
