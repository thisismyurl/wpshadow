<?php
/**
 * Serverpilot Cache Settings Diagnostic
 *
 * Serverpilot Cache Settings needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1030.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serverpilot Cache Settings Diagnostic Class
 *
 * @since 1.1030.0000
 */
class Diagnostic_ServerpilotCacheSettings extends Diagnostic_Base {

	protected static $slug = 'serverpilot-cache-settings';
	protected static $title = 'Serverpilot Cache Settings';
	protected static $description = 'Serverpilot Cache Settings needs attention';
	protected static $family = 'performance';

	public static function check() {
		// Check for ServerPilot environment
		$is_serverpilot = file_exists( '/etc/nginx-sp' ) ||
		                  defined( 'SERVERPILOT_APP_NAME' ) ||
		                  strpos( $_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx-sp' ) !== false;
		
		if ( ! $is_serverpilot ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Nginx cache enabled
		$cache_enabled = get_option( 'serverpilot_cache_enabled', false );
		if ( ! $cache_enabled ) {
			$issues[] = __( 'Nginx caching not enabled (missing performance)', 'wpshadow' );
		}
		
		// Check 2: Cache TTL
		$cache_ttl = get_option( 'serverpilot_cache_ttl', 3600 );
		if ( $cache_ttl > 86400 ) {
			$issues[] = sprintf( __( 'Cache TTL: %d seconds (stale content risk)', 'wpshadow' ), $cache_ttl );
		}
		
		// Check 3: Logged-in user caching
		$cache_logged_in = get_option( 'serverpilot_cache_logged_in', false );
		if ( $cache_logged_in ) {
			$issues[] = __( 'Caching logged-in users (personalization broken)', 'wpshadow' );
		}
		
		// Check 4: Cache exclusions
		$exclusions = get_option( 'serverpilot_cache_exclusions', array() );
		$required_exclusions = array( 'wp-admin', 'wp-login.php', 'cart', 'checkout', 'my-account' );
		
		$missing_exclusions = array();
		foreach ( $required_exclusions as $path ) {
			if ( ! in_array( $path, $exclusions, true ) ) {
				$missing_exclusions[] = $path;
			}
		}
		
		if ( count( $missing_exclusions ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %s: list of missing exclusions */
				__( 'Missing cache exclusions: %s', 'wpshadow' ),
				implode( ', ', $missing_exclusions )
			);
		}
		
		// Check 5: Automatic cache purging
		$auto_purge = get_option( 'serverpilot_auto_purge', true );
		if ( ! $auto_purge ) {
			$issues[] = __( 'Auto-purge disabled (manual cache clearing)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of cache issues */
				__( 'ServerPilot cache has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/serverpilot-cache-settings',
		);
	}
}
