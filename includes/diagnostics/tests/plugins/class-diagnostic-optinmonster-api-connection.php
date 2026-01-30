<?php
/**
 * OptinMonster API Connection Diagnostic
 *
 * OptinMonster API key not configured or connection failing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.218.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OptinMonster API Connection Diagnostic Class
 *
 * @since 1.218.0000
 */
class Diagnostic_OptinmonsterApiConnection extends Diagnostic_Base {

	protected static $slug = 'optinmonster-api-connection';
	protected static $title = 'OptinMonster API Connection';
	protected static $description = 'OptinMonster API key not configured or connection failing';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'OMAPI_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API credentials configured
		$api_credentials = get_option( 'optin_monster_api', array() );
		if ( empty( $api_credentials ) || ! isset( $api_credentials['api_key'] ) ) {
			$issues[] = __( 'OptinMonster API key not configured', 'wpshadow' );
		}
		
		// Check 2: API connection status
		$connection_status = get_option( 'omapi_connection_status', '' );
		if ( $connection_status !== 'connected' ) {
			$issues[] = sprintf( __( 'API connection status: %s', 'wpshadow' ), empty( $connection_status ) ? 'unknown' : $connection_status );
		}
		
		// Check 3: Last successful API sync
		$last_sync = get_option( 'omapi_last_sync', 0 );
		if ( $last_sync > 0 ) {
			$hours_since = ( time() - $last_sync ) / 3600;
			if ( $hours_since > 24 ) {
				$issues[] = sprintf( __( 'Last API sync: %.1f hours ago (campaigns may be outdated)', 'wpshadow' ), $hours_since );
			}
		} else {
			$issues[] = __( 'No successful API sync recorded', 'wpshadow' );
		}
		
		// Check 4: Campaign data cached
		global $wpdb;
		$campaigns = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				'omapi_campaign_%'
			)
		);
		
		if ( $campaigns === 0 ) {
			$issues[] = __( 'No campaigns cached locally', 'wpshadow' );
		}
		
		// Check 5: API error logs
		$error_log = get_option( 'omapi_error_log', array() );
		if ( is_array( $error_log ) && ! empty( $error_log ) ) {
			$recent_errors = array_slice( $error_log, -5 );
			$issues[] = sprintf( __( '%d API errors logged (most recent: %s)', 'wpshadow' ), count( $error_log ), end( $recent_errors ) );
		}
		
		// Check 6: SSL verification for API calls
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using SSL (API credentials transmitted insecurely)', 'wpshadow' );
		}
		
		// Check 7: API rate limiting
		$rate_limit_hit = get_transient( 'omapi_rate_limit_exceeded' );
		if ( $rate_limit_hit ) {
			$issues[] = __( 'API rate limit exceeded (campaigns may not update)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 60;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 75;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 68;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of connection issues */
				__( 'OptinMonster API connection has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/optinmonster-api-connection',
		);
	}
}
