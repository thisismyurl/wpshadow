<?php
/**
 * Site Uptime History Diagnostic
 *
 * Tracks site uptime over time to detect hosting reliability issues
 * and provide uptime percentage KPIs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Uptime History Class
 *
 * Monitors uptime percentage and detects downtime patterns.
 * Provides early warning of hosting/configuration issues.
 *
 * @since 1.5029.1045
 */
class Diagnostic_Uptime_History extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-uptime-history';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Uptime History';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tracks uptime percentage and reliability metrics';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes uptime data stored in options table using get_option().
	 * Calculates 24h, 7d, and 30d uptime percentages.
	 *
	 * @since  1.5029.1045
	 * @return array|null Finding array if poor uptime detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_uptime_history_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get uptime tracking data using WordPress API (NO $wpdb).
		$uptime_log = get_option( 'wpshadow_uptime_log', array() );

		if ( empty( $uptime_log ) || ! is_array( $uptime_log ) ) {
			// No tracking data yet - initialize tracking.
			self::record_uptime_check( true );
			set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		// Record current uptime check.
		$current_status = self::test_site_reachability();
		self::record_uptime_check( $current_status );

		// Calculate uptime percentages for different periods.
		$uptime_24h = self::calculate_uptime_percentage( $uptime_log, DAY_IN_SECONDS );
		$uptime_7d  = self::calculate_uptime_percentage( $uptime_log, 7 * DAY_IN_SECONDS );
		$uptime_30d = self::calculate_uptime_percentage( $uptime_log, 30 * DAY_IN_SECONDS );

		$issues = array();

		// Flag if uptime is below acceptable thresholds.
		if ( $uptime_24h < 99.0 ) {
			$issues[] = sprintf(
				/* translators: %s: uptime percentage */
				__( '24-hour uptime: %s%% (below 99%%)', 'wpshadow' ),
				number_format( $uptime_24h, 2 )
			);
		}

		if ( $uptime_7d < 99.5 ) {
			$issues[] = sprintf(
				/* translators: %s: uptime percentage */
				__( '7-day uptime: %s%% (below 99.5%%)', 'wpshadow' ),
				number_format( $uptime_7d, 2 )
			);
		}

		if ( $uptime_30d < 99.9 ) {
			$issues[] = sprintf(
				/* translators: %s: uptime percentage */
				__( '30-day uptime: %s%% (below 99.9%%)', 'wpshadow' ),
				number_format( $uptime_30d, 2 )
			);
		}

		// Detect downtime patterns.
		$downtime_events = self::detect_downtime_events( $uptime_log );
		if ( $downtime_events > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of downtime events */
				__( '%d downtime events detected in recent history', 'wpshadow' ),
				$downtime_events
			);
		}

		// If issues found, flag it.
		if ( ! empty( $issues ) ) {
			$threat_level = 30;
			if ( $uptime_7d < 98.0 ) {
				$threat_level = 50;
			}
			if ( $uptime_24h < 95.0 ) {
				$threat_level = 65;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: uptime percentage */
					__( 'Site reliability below target. 30-day uptime: %s%%. Review hosting performance.', 'wpshadow' ),
					number_format( $uptime_30d, 2 )
				),
				'severity'     => $threat_level > 50 ? 'high' : 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/monitoring-uptime-history',
				'data'         => array(
					'uptime_24h'       => $uptime_24h,
					'uptime_7d'        => $uptime_7d,
					'uptime_30d'       => $uptime_30d,
					'downtime_events'  => $downtime_events,
					'issues'           => $issues,
					'total_checks'     => count( $uptime_log ),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Test if site is reachable.
	 *
	 * @since  1.5029.1045
	 * @return bool True if reachable, false otherwise.
	 */
	private static function test_site_reachability() {
		$response = wp_remote_get( home_url(), array(
			'timeout'   => 10,
			'sslverify' => false,
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		return $code >= 200 && $code < 400;
	}

	/**
	 * Record uptime check.
	 *
	 * @since 1.5029.1045
	 * @param bool $status True if up, false if down.
	 */
	private static function record_uptime_check( $status ) {
		$uptime_log = get_option( 'wpshadow_uptime_log', array() );

		$uptime_log[] = array(
			'timestamp' => time(),
			'status'    => $status ? 'up' : 'down',
		);

		// Keep only last 30 days of data.
		$cutoff = time() - ( 30 * DAY_IN_SECONDS );
		$uptime_log = array_filter( $uptime_log, function( $entry ) use ( $cutoff ) {
			return $entry['timestamp'] > $cutoff;
		} );

		update_option( 'wpshadow_uptime_log', array_values( $uptime_log ), false );
	}

	/**
	 * Calculate uptime percentage for period.
	 *
	 * @since  1.5029.1045
	 * @param  array $log     Uptime log entries.
	 * @param  int   $seconds Period in seconds.
	 * @return float Uptime percentage.
	 */
	private static function calculate_uptime_percentage( $log, $seconds ) {
		$cutoff = time() - $seconds;
		$recent_entries = array_filter( $log, function( $entry ) use ( $cutoff ) {
			return $entry['timestamp'] > $cutoff;
		} );

		if ( empty( $recent_entries ) ) {
			return 100.0;
		}

		$up_count = 0;
		foreach ( $recent_entries as $entry ) {
			if ( 'up' === $entry['status'] ) {
				$up_count++;
			}
		}

		return ( $up_count / count( $recent_entries ) ) * 100;
	}

	/**
	 * Detect downtime events.
	 *
	 * @since  1.5029.1045
	 * @param  array $log Uptime log entries.
	 * @return int Number of downtime events.
	 */
	private static function detect_downtime_events( $log ) {
		$events = 0;
		$previous_status = 'up';

		foreach ( $log as $entry ) {
			if ( 'down' === $entry['status'] && 'up' === $previous_status ) {
				$events++;
			}
			$previous_status = $entry['status'];
		}

		return $events;
	}
}
