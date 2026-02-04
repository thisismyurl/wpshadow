<?php
/**
 * Murphy-Safe Database Operations
 *
 * Defensive database operation wrappers that implement Murphy's Law principles:
 * - Assume database will fail or be unavailable
 * - Verify all writes succeeded
 * - Rollback on corruption
 * - Queue for retry on failure
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.6035.1510
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Murphy_Safe_Database Class
 *
 * Provides resilient database operations with verification,
 * rollback, and retry capabilities.
 *
 * Philosophy Alignment:
 * - ⚙️ Murphy's Law: Assume database operations will fail
 * - #8 Inspire Confidence: Users trust data won't be lost
 * - #1 Helpful Neighbor: Transparent recovery process
 *
 * @since 1.6035.1510
 */
class Murphy_Safe_Database {

	/**
	 * Safely update an option with verification and rollback
	 *
	 * Process:
	 * 1. Create checkpoint of current value
	 * 2. Attempt update
	 * 3. Verify update succeeded
	 * 4. Rollback if corruption detected
	 * 5. Queue retry if failed
	 *
	 * @since  1.6035.1510
	 * @param  string $option Option name.
	 * @param  mixed  $value  New value.
	 * @param  bool   $autoload Optional. Whether to autoload. Default null (unchanged).
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function update_option_safe( $option, $value, $autoload = null ) {
		// Create checkpoint before changes.
		$checkpoint = get_option( $option );
		update_option( "{$option}_checkpoint", $checkpoint, false );

		// Record attempt.
		Error_Handler::log_info(
			'Attempting option update',
			array(
				'option'    => $option,
				'old_value' => $checkpoint,
				'new_value' => $value,
			)
		);

		// Try primary save.
		if ( null === $autoload ) {
			$result = update_option( $option, $value );
		} else {
			$result = update_option( $option, $value, $autoload );
		}

		// Check if update returned false (means no change or error).
		if ( false === $result ) {
			// Could be "no change" or actual failure. Verify.
			$current = get_option( $option );

			// If value matches, no change was needed (not an error).
			if ( $current === $value ) {
				// Clean up checkpoint.
				delete_option( "{$option}_checkpoint" );
				return true;
			}

			// Value doesn't match - actual failure.
			Error_Handler::log_warning(
				'Option update failed, attempting recovery',
				array(
					'option'          => $option,
					'expected_value'  => $value,
					'actual_value'    => $current,
				)
			);

			// Try transient as backup.
			set_transient( "{$option}_pending", $value, HOUR_IN_SECONDS );

			// Queue for retry.
			self::queue_option_retry( $option, $value, $autoload );

			return new \WP_Error(
				'option_save_failed',
				sprintf(
					/* translators: %s: option name */
					__( 'Settings for "%s" saved temporarily. Will retry automatically.', 'wpshadow' ),
					$option
				),
				array(
					'option' => $option,
					'value'  => $value,
				)
			);
		}

		// Update reported success - verify it actually worked.
		$saved = get_option( $option );

		// Strict comparison for verification.
		if ( $saved !== $value ) {
			// Data corruption detected!
			Error_Handler::log_error(
				'Data corruption detected after option update',
				array(
					'option'         => $option,
					'expected_value' => $value,
					'actual_value'   => $saved,
				)
			);

			// Restore checkpoint.
			update_option( $option, $checkpoint, false );

			// Queue for retry.
			self::queue_option_retry( $option, $value, $autoload );

			return new \WP_Error(
				'data_corruption',
				sprintf(
					/* translators: %s: option name */
					__( 'Settings save verification failed for "%s". Restored previous settings.', 'wpshadow' ),
					$option
				),
				array(
					'option'   => $option,
					'expected' => $value,
					'actual'   => $saved,
				)
			);
		}

		// Success! Clean up.
		delete_option( "{$option}_checkpoint" );
		delete_transient( "{$option}_pending" );

		Error_Handler::log_info(
			'Option updated and verified successfully',
			array( 'option' => $option )
		);

		return true;
	}

	/**
	 * Queue a failed option update for retry
	 *
	 * @since  1.6035.1510
	 * @param  string $option   Option name.
	 * @param  mixed  $value    Value to save.
	 * @param  bool   $autoload Autoload setting.
	 * @return bool True if queued.
	 */
	private static function queue_option_retry( $option, $value, $autoload ) {
		$queue = get_option( 'wpshadow_option_retry_queue', array() );

		$queue[] = array(
			'option'       => $option,
			'value'        => $value,
			'autoload'     => $autoload,
			'queued_at'    => current_time( 'timestamp' ),
			'attempts'     => 0,
			'max_attempts' => 5,
		);

		// Limit queue size.
		if ( count( $queue ) > 100 ) {
			array_shift( $queue );
		}

		return update_option( 'wpshadow_option_retry_queue', $queue, false );
	}

	/**
	 * Process option retry queue (called by cron)
	 *
	 * @since  1.6035.1510
	 * @return array Processing stats.
	 */
	public static function process_option_retry_queue() {
		$queue = get_option( 'wpshadow_option_retry_queue', array() );

		if ( empty( $queue ) ) {
			return array(
				'processed' => 0,
				'succeeded' => 0,
				'failed'    => 0,
				'removed'   => 0,
			);
		}

		$stats     = array(
			'processed' => 0,
			'succeeded' => 0,
			'failed'    => 0,
			'removed'   => 0,
		);
		$new_queue = array();
		$now       = current_time( 'timestamp' );

		foreach ( $queue as $retry ) {
			$stats['processed']++;

			// Remove if too old (24 hours).
			if ( $now - $retry['queued_at'] > DAY_IN_SECONDS ) {
				$stats['removed']++;
				continue;
			}

			// Remove if too many attempts.
			if ( $retry['attempts'] >= $retry['max_attempts'] ) {
				$stats['removed']++;
				Error_Handler::log_warning(
					'Option update removed from retry queue after max attempts',
					array( 'option' => $retry['option'] )
				);
				continue;
			}

			// Attempt to save.
			$result = null === $retry['autoload']
				? update_option( $retry['option'], $retry['value'] )
				: update_option( $retry['option'], $retry['value'], $retry['autoload'] );

			if ( false !== $result ) {
				// Verify.
				$saved = get_option( $retry['option'] );
				if ( $saved === $retry['value'] ) {
					$stats['succeeded']++;
					// Don't re-queue.
					continue;
				}
			}

			// Failed, re-queue.
			$retry['attempts']++;
			$new_queue[] = $retry;
			$stats['failed']++;
		}

		// Update queue.
		update_option( 'wpshadow_option_retry_queue', $new_queue, false );

		// Log results.
		if ( $stats['succeeded'] > 0 || $stats['failed'] > 0 ) {
			Error_Handler::log_info( 'Option retry queue processed', $stats );
		}

		return $stats;
	}

	/**
	 * Safely add option with existence check
	 *
	 * @since  1.6035.1510
	 * @param  string $option   Option name.
	 * @param  mixed  $value    Option value.
	 * @param  bool   $autoload Whether to autoload.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function add_option_safe( $option, $value, $autoload = true ) {
		// Check if already exists.
		$existing = get_option( $option, '__wpshadow_not_found__' );

		if ( '__wpshadow_not_found__' !== $existing ) {
			return new \WP_Error(
				'option_already_exists',
				sprintf(
					/* translators: %s: option name */
					__( 'Option "%s" already exists. Use update instead.', 'wpshadow' ),
					$option
				)
			);
		}

		$result = add_option( $option, $value, '', $autoload );

		if ( false === $result ) {
			Error_Handler::log_warning(
				'Failed to add option',
				array( 'option' => $option )
			);

			return new \WP_Error(
				'option_add_failed',
				sprintf(
					/* translators: %s: option name */
					__( 'Failed to add option "%s".', 'wpshadow' ),
					$option
				)
			);
		}

		// Verify.
		$saved = get_option( $option );
		if ( $saved !== $value ) {
			// Corruption.
			delete_option( $option );

			return new \WP_Error(
				'option_add_verification_failed',
				sprintf(
					/* translators: %s: option name */
					__( 'Option "%s" verification failed. Add cancelled.', 'wpshadow' ),
					$option
				)
			);
		}

		return true;
	}

	/**
	 * Safely delete option with verification
	 *
	 * @since  1.6035.1510
	 * @param  string $option Option name.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function delete_option_safe( $option ) {
		// Create backup before deletion.
		$backup = get_option( $option );
		update_option( "{$option}_deleted_backup", $backup, false );

		$result = delete_option( $option );

		if ( false === $result ) {
			Error_Handler::log_warning(
				'Failed to delete option',
				array( 'option' => $option )
			);

			return new \WP_Error(
				'option_delete_failed',
				sprintf(
					/* translators: %s: option name */
					__( 'Failed to delete option "%s".', 'wpshadow' ),
					$option
				)
			);
		}

		// Verify deletion.
		$still_exists = get_option( $option, '__wpshadow_not_found__' );
		if ( '__wpshadow_not_found__' !== $still_exists ) {
			Error_Handler::log_error(
				'Option delete verification failed - option still exists',
				array( 'option' => $option )
			);

			return new \WP_Error(
				'option_delete_verification_failed',
				sprintf(
					/* translators: %s: option name */
					__( 'Option "%s" delete verification failed.', 'wpshadow' ),
					$option
				)
			);
		}

		return true;
	}

	/**
	 * Register cron jobs for retry processing
	 *
	 * @since 1.6035.1510
	 * @return void
	 */
	public static function register_cron() {
		if ( ! wp_next_scheduled( 'wpshadow_process_option_retry_queue' ) ) {
			wp_schedule_event( time(), 'wpshadow_5min', 'wpshadow_process_option_retry_queue' );
		}

		add_action( 'wpshadow_process_option_retry_queue', array( __CLASS__, 'process_option_retry_queue' ) );
	}
}
