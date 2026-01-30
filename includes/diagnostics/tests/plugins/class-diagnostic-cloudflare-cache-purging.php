<?php
/**
 * Cloudflare Cache Purging Diagnostic
 *
 * Cloudflare Cache Purging needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.989.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudflare Cache Purging Diagnostic Class
 *
 * @since 1.989.0000
 */
class Diagnostic_CloudflareCachePurging extends Diagnostic_Base {

	protected static $slug = 'cloudflare-cache-purging';
	protected static $title = 'Cloudflare Cache Purging';
	protected static $description = 'Cloudflare Cache Purging needs attention';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'CLOUDFLARE_VERSION' ) && ! class_exists( 'CF\WordPress\Hooks' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Cloudflare API credentials
		$cf_settings = get_option( 'cloudflare_settings', array() );
		if ( empty( $cf_settings['api_key'] ) && empty( $cf_settings['api_token'] ) ) {
			$issues[] = 'Cloudflare API credentials not configured';
		}
		
		// Check 2: Automatic cache purging
		$auto_purge = get_option( 'cloudflare_auto_purge', '0' );
		if ( '0' === $auto_purge ) {
			$issues[] = 'automatic cache purging disabled (stale content may be served)';
		}
		
		// Check 3: Purge on post update
		$purge_on_update = get_option( 'cloudflare_purge_on_update', '0' );
		if ( '0' === $purge_on_update ) {
			$issues[] = 'cache not purged on post updates';
		}
		
		// Check 4: Failed purge attempts
		$purge_errors = get_transient( 'cloudflare_purge_errors' );
		if ( ! empty( $purge_errors ) && is_array( $purge_errors ) ) {
			$error_count = count( $purge_errors );
			if ( $error_count > 5 ) {
				$issues[] = "{$error_count} recent cache purge failures";
			}
		}
		
		// Check 5: Zone ID configuration
		$zone_id = get_option( 'cloudflare_zone_id', '' );
		if ( empty( $zone_id ) ) {
			$issues[] = 'Cloudflare zone ID not configured';
		}
		
		// Check 6: Cache level setting
		$cache_level = get_option( 'cloudflare_cache_level', '' );
		if ( ! empty( $cf_settings ) ) {
			if ( empty( $cache_level ) || 'aggressive' !== $cache_level ) {
				$current = ! empty( $cache_level ) ? $cache_level : 'not set';
				$issues[] = "cache level '{$current}' (consider 'aggressive' for better performance)";
			}
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Cloudflare cache purging performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cloudflare-cache-purging',
			);
		}
		
		return null;
	}
}
