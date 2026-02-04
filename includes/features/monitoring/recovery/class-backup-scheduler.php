<?php
/**
 * Scheduled Backup Scheduler (Vault Light)
 *
 * Handles scheduled backup snapshots that feel like Vault, but lighter.
 * Uses Backup_Manager to store settings snapshots with configurable retention.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.6030.0218
 */

declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Scheduler
 *
 * @since 1.6030.0218
 */
class Backup_Scheduler {
	/**
	 * Cron hook name for scheduled backups.
	 *
	 * @var string
	 */
	private const HOOK = 'wpshadow_scheduled_backup';

	/**
	 * Initialize scheduler hooks.
	 *
	 * @since 1.6030.0218
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'cron_schedules', array( __CLASS__, 'register_cron_schedules' ) );
		add_action( 'init', array( __CLASS__, 'maybe_schedule' ) );
		add_action( self::HOOK, array( __CLASS__, 'run_scheduled_backup' ) );

		add_action( 'wpshadow_setting_updated_wpshadow_backup_schedule_enabled', array( __CLASS__, 'reschedule' ), 10, 2 );
		add_action( 'wpshadow_setting_updated_wpshadow_backup_schedule_frequency', array( __CLASS__, 'reschedule' ), 10, 2 );
		add_action( 'wpshadow_setting_updated_wpshadow_backup_schedule_time', array( __CLASS__, 'reschedule' ), 10, 2 );
	}

	/**
	 * Register cron schedules for weekly and monthly backups.
	 *
	 * @since 1.6030.0218
	 * @param array $schedules Existing schedules.
	 * @return array
	 */
	public static function register_cron_schedules( array $schedules ): array {
		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = array(
				'interval' => WEEK_IN_SECONDS,
				'display'  => __( 'Once Weekly', 'wpshadow' ),
			);
		}

		if ( ! isset( $schedules['monthly'] ) ) {
			$schedules['monthly'] = array(
				'interval' => DAY_IN_SECONDS * 30,
				'display'  => __( 'Once Monthly', 'wpshadow' ),
			);
		}

		return $schedules;
	}

	/**
	 * Ensure schedule exists if enabled.
	 *
	 * @since 1.6030.0218
	 * @return void
	 */
	public static function maybe_schedule(): void {
		if ( ! self::is_enabled() ) {
			return;
		}

		if ( wp_next_scheduled( self::HOOK ) ) {
			return;
		}

		self::schedule_next_run();
	}

	/**
	 * Reschedule backup job when settings change.
	 *
	 * @since 1.6030.0218
	 * @return void
	 */
	public static function reschedule(): void {
		wp_clear_scheduled_hook( self::HOOK );

		if ( ! self::is_enabled() ) {
			return;
		}

		self::schedule_next_run();
	}

	/**
	 * Run the scheduled backup.
	 *
	 * @since 1.6030.0218
	 * @return void
	 */
	public static function run_scheduled_backup(): void {
		if ( ! self::is_enabled() ) {
			return;
		}

		$retention_days = absint( get_option( 'wpshadow_backup_retention_days', 7 ) );
		$backup_id      = Backup_Manager::create_automated_backup( 'scheduled_backup', $retention_days );

		update_option( 'wpshadow_backup_last_run', time() );
		do_action( 'wpshadow_backup_completed', $backup_id );

		if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
			Activity_Logger::log(
				'backup_scheduled',
				__( 'Scheduled Vault Light backup completed.', 'wpshadow' ),
				'recovery',
				array(
					'backup_id'      => $backup_id,
					'retention_days' => $retention_days,
				)
			);
		}

		self::schedule_next_run();
	}

	/**
	 * Determine if scheduled backups are enabled.
	 *
	 * @since 1.6030.0218
	 * @return bool
	 */
	private static function is_enabled(): bool {
		return (bool) get_option( 'wpshadow_backup_schedule_enabled', false );
	}

	/**
	 * Schedule the next run based on current settings.
	 *
	 * @since 1.6030.0218
	 * @return void
	 */
	private static function schedule_next_run(): void {
		$frequency = get_option( 'wpshadow_backup_schedule_frequency', 'weekly' );
		$time      = get_option( 'wpshadow_backup_schedule_time', '02:00' );

		$timestamp = self::get_next_timestamp( $time, $frequency );
		wp_schedule_event( $timestamp, $frequency, self::HOOK );
	}

	/**
	 * Get the next timestamp based on time and frequency.
	 *
	 * @since 1.6030.0218
	 * @param string $time Time string (HH:MM).
	 * @param string $frequency Frequency slug.
	 * @return int Timestamp.
	 */
	private static function get_next_timestamp( string $time, string $frequency ): int {
		$time = preg_match( '/^([01]\d|2[0-3]):([0-5]\d)$/', $time ) ? $time : '02:00';
		$tz   = wp_timezone();
		$now  = new \DateTimeImmutable( 'now', $tz );
		$base = new \DateTimeImmutable( $now->format( 'Y-m-d' ) . ' ' . $time, $tz );

		if ( $base <= $now ) {
			switch ( $frequency ) {
				case 'monthly':
					$base = $base->modify( '+1 month' );
					break;
				case 'weekly':
					$base = $base->modify( '+1 week' );
					break;
				case 'daily':
				default:
					$base = $base->modify( '+1 day' );
					break;
			}
		}

		return $base->getTimestamp();
	}
}
