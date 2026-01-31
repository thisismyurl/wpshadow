<?php
/**
 * Wp Crontrol Orphaned Cron Diagnostic
 *
 * Wp Crontrol Orphaned Cron issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1045.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Crontrol Orphaned Cron Diagnostic Class
 *
 * @since 1.1045.0000
 */
class Diagnostic_WpCrontrolOrphanedCron extends Diagnostic_Base {

	protected static $slug = 'wp-crontrol-orphaned-cron';
	protected static $title = 'Wp Crontrol Orphaned Cron';
	protected static $description = 'Wp Crontrol Orphaned Cron issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'crontrol_get_all_events' ) && ! class_exists( 'Crontrol\Event' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Orphaned cron jobs.
		$crons = _get_cron_array();
		$orphaned_count = 0;
		if ( ! empty( $crons ) ) {
			foreach ( $crons as $timestamp => $cron ) {
				foreach ( $cron as $hook => $events ) {
					if ( ! has_action( $hook ) ) {
						$orphaned_count++;
					}
				}
			}
		}
		if ( $orphaned_count > 0 ) {
			$issues[] = "{$orphaned_count} orphaned cron jobs";
		}

		// Check 2: Auto cleanup.
		$auto_cleanup = get_option( 'crontrol_auto_cleanup', '0' );
		if ( '0' === $auto_cleanup ) {
			$issues[] = 'auto cleanup disabled';
		}

		// Check 3: Failed job logging.
		$log_failed = get_option( 'crontrol_log_failed', '1' );
		if ( '0' === $log_failed ) {
			$issues[] = 'failed job logging disabled';
		}

		// Check 4: Execution monitoring.
		$monitor = get_option( 'crontrol_monitor_execution', '0' );
		if ( '0' === $monitor ) {
			$issues[] = 'execution monitoring disabled';
		}

		// Check 5: Missed schedules.
		$missed = get_option( 'crontrol_check_missed', '1' );
		if ( '0' === $missed ) {
			$issues[] = 'missed schedule detection disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WP Crontrol issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-crontrol-orphaned-cron',
			);
		}

		return null;
	}
}
