<?php
/**
 * Local Backup Scheduler.
 *
 * Wires the Vault Lite local-only backup engine into WordPress cron and
 * keeps the next scheduled backup aligned with the current settings.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since   0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Guardian;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schedule and run local-only Vault Lite backups.
 */
class Backup_Scheduler {

	/**
	 * Cron hook used for scheduled local backups.
	 *
	 * @var string
	 */
	private const CRON_HOOK = 'wpshadow_run_scheduled_backup';

	/**
	 * Option storing the active schedule signature.
	 *
	 * @var string
	 */
	private const OPTION_SIGNATURE = 'wpshadow_backup_schedule_signature';

	/**
	 * Whether hooks have already been registered for this request.
	 *
	 * @var bool
	 */
	private static $bootstrapped = false;

	/**
	 * Bootstrap the backup scheduler.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function init(): void {
		if ( self::$bootstrapped ) {
			return;
		}

		self::$bootstrapped = true;

		Backup_Manager::init();
		add_action( 'init', array( __CLASS__, 'sync_schedule' ), 25 );
		add_action( self::CRON_HOOK, array( __CLASS__, 'run_scheduled_backup' ) );
	}

	/**
	 * Keep the WordPress cron event aligned with current backup settings.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function sync_schedule(): void {
		if ( ! (bool) get_option( 'wpshadow_backup_schedule_enabled', false ) ) {
			self::clear_scheduled_backups();
			delete_option( self::OPTION_SIGNATURE );
			return;
		}

		$current_signature = self::get_schedule_signature();
		$stored_signature  = (string) get_option( self::OPTION_SIGNATURE, '' );

		if ( $stored_signature !== $current_signature ) {
			self::clear_scheduled_backups();
			update_option( self::OPTION_SIGNATURE, $current_signature, false );
		}

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			self::schedule_next_event();
		}
	}

	/**
	 * Run a scheduled local backup and queue the next one.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function run_scheduled_backup(): void {
		Backup_Manager::create_backup(
			array(
				'trigger' => 'scheduled',
				'context' => 'wp-cron',
			)
		);

		self::schedule_next_event();
	}

	/**
	 * Get the next scheduled backup timestamp.
	 *
	 * @since  0.6093.1200
	 * @return int Unix timestamp, or 0 when no backup is scheduled.
	 */
	public static function get_next_scheduled_timestamp(): int {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		return false === $timestamp ? 0 : (int) $timestamp;
	}

	/**
	 * Get the next scheduled backup as a human-readable string.
	 *
	 * @since  0.6093.1200
	 * @return string Human-readable scheduled time.
	 */
	public static function get_next_scheduled_display(): string {
		$timestamp = self::get_next_scheduled_timestamp();
		if ( $timestamp <= 0 ) {
			return __( 'Not scheduled', 'wpshadow' );
		}

		return wp_date( get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', 'H:i' ), $timestamp );
	}

	/**
	 * Clear all queued scheduled local backups.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	private static function clear_scheduled_backups(): void {
		while ( false !== wp_next_scheduled( self::CRON_HOOK ) ) {
			$timestamp = wp_next_scheduled( self::CRON_HOOK );
			if ( false === $timestamp ) {
				break;
			}

			wp_unschedule_event( (int) $timestamp, self::CRON_HOOK );
		}
	}

	/**
	 * Schedule the next local backup event as a single cron run.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	private static function schedule_next_event(): void {
		$timestamp = self::calculate_next_run_timestamp();
		if ( $timestamp > 0 ) {
			wp_schedule_single_event( $timestamp, self::CRON_HOOK );
		}
	}

	/**
	 * Calculate the next run timestamp from the configured frequency and time.
	 *
	 * @since  0.6093.1200
	 * @return int Unix timestamp for the next scheduled backup.
	 */
	private static function calculate_next_run_timestamp(): int {
		$frequency = (string) get_option( 'wpshadow_backup_schedule_frequency', 'daily' );
		$time      = (string) get_option( 'wpshadow_backup_schedule_time', '02:00' );
		$timezone  = function_exists( 'wp_timezone' ) ? wp_timezone() : new \DateTimeZone( 'UTC' );
		$now       = new \DateTimeImmutable( 'now', $timezone );

		$time_parts = array_map( 'absint', explode( ':', $time ) );
		$hour       = $time_parts[0] ?? 2;
		$minute     = $time_parts[1] ?? 0;
		$candidate  = $now->setTime( $hour, $minute, 0 );

		if ( $candidate <= $now ) {
			switch ( $frequency ) {
				case 'weekly':
					$candidate = $candidate->modify( '+1 week' );
					break;
				case 'monthly':
					$candidate = $candidate->modify( '+1 month' );
					break;
				case 'daily':
				default:
					$candidate = $candidate->modify( '+1 day' );
					break;
			}
		}

		return $candidate->getTimestamp();
	}

	/**
	 * Build a signature representing the active schedule settings.
	 *
	 * @since  0.6093.1200
	 * @return string Schedule signature hash.
	 */
	private static function get_schedule_signature(): string {
		return md5(
			(string) wp_json_encode(
				array(
					'enabled'   => (bool) get_option( 'wpshadow_backup_schedule_enabled', false ),
					'frequency' => (string) get_option( 'wpshadow_backup_schedule_frequency', 'daily' ),
					'time'      => (string) get_option( 'wpshadow_backup_schedule_time', '02:00' ),
				)
			)
		);
	}
}
