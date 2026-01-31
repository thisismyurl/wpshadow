<?php
/**
 * Wp Crontrol Scheduled Events Diagnostic
 *
 * Wp Crontrol Scheduled Events issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1044.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Crontrol Scheduled Events Diagnostic Class
 *
 * @since 1.1044.0000
 */
class Diagnostic_WpCrontrolScheduledEvents extends Diagnostic_Base {

	protected static $slug = 'wp-crontrol-scheduled-events';
	protected static $title = 'Wp Crontrol Scheduled Events';
	protected static $description = 'Wp Crontrol Scheduled Events issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'wp_get_schedules' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify excessive scheduled events
		$crons = _get_cron_array();
		$total_events = 0;
		if ( is_array( $crons ) ) {
			foreach ( $crons as $timestamp => $cronhooks ) {
				$total_events += count( $cronhooks );
			}
		}
		if ( $total_events > 100 ) {
			$issues[] = __( 'Excessive scheduled events detected', 'wpshadow' );
		}

		// Check 2: Check for duplicate scheduled events
		$event_hashes = array();
		if ( is_array( $crons ) ) {
			foreach ( $crons as $timestamp => $cronhooks ) {
				foreach ( $cronhooks as $hook => $events ) {
					foreach ( $events as $event ) {
						$hash = md5( serialize( array( $hook, $event['args'] ) ) );
						if ( isset( $event_hashes[ $hash ] ) ) {
							$issues[] = __( 'Duplicate scheduled events found', 'wpshadow' );
							break 3;
						}
						$event_hashes[ $hash ] = true;
					}
				}
			}
		}

		// Check 3: Verify no missed scheduled events
		if ( is_array( $crons ) ) {
			$now = time();
			foreach ( $crons as $timestamp => $cronhooks ) {
				if ( $timestamp < ( $now - HOUR_IN_SECONDS ) ) {
					$issues[] = __( 'Missed scheduled events detected', 'wpshadow' );
					break;
				}
			}
		}

		// Check 4: Check cron lock status
		$doing_cron = get_transient( 'doing_cron' );
		if ( $doing_cron && $doing_cron < ( time() - 600 ) ) {
			$issues[] = __( 'Cron lock appears to be stuck', 'wpshadow' );
		}

		// Check 5: Verify reasonable event frequency
		$schedules = wp_get_schedules();
		$high_frequency_count = 0;
		if ( is_array( $crons ) ) {
			foreach ( $crons as $timestamp => $cronhooks ) {
				foreach ( $cronhooks as $hook => $events ) {
					foreach ( $events as $event ) {
						if ( isset( $event['schedule'] ) && isset( $schedules[ $event['schedule'] ] ) ) {
							if ( $schedules[ $event['schedule'] ]['interval'] < 300 ) {
								$high_frequency_count++;
							}
						}
					}
				}
			}
		}
		if ( $high_frequency_count > 10 ) {
			$issues[] = __( 'Too many high-frequency scheduled events', 'wpshadow' );
		}

		// Check 6: Check scheduled action performance monitoring
		$cron_monitoring = get_option( 'wpcrontrol_monitoring_enabled', false );
		if ( ! $cron_monitoring ) {
			$issues[] = __( 'Cron event monitoring not enabled', 'wpshadow' );
		}
		return null;
	}
}
