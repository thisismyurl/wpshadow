<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\KPI_Tracker;

/**
 * Guardian Recovery System
 *
 * Manages backup/restore for treatment rollback.
 * Allows users to undo any auto-fix that goes wrong.
 *
 * Features:
 * - Create pre-fix backups
 * - Restore from backups
 * - Backup cleanup
 * - Recovery logging
 * - Rollback safety
 *
 * Philosophy: All changes are reversible.
 * User always has control to undo.
 */
class Recovery_System {

	/**
	 * Create recovery point before fix
	 *
	 * Stores current option values for restoration.
	 *
	 * @param string $reason Reason for backup
	 * @param string $description Optional description
	 *
	 * @return string Backup ID for reference
	 */
	public static function create_recovery_point( string $reason, string $description = '' ): string {
		$backup_id = 'recovery_' . time() . '_' . wp_generate_password( 8, false );

		$backup = array(
			'id'          => $backup_id,
			'timestamp'   => current_time( 'mysql' ),
			'reason'      => sanitize_text_field( $reason ),
			'description' => sanitize_text_field( $description ),
			'snapshot'    => self::capture_snapshot(),
		);

		// Store in transient (28 days)
		set_transient(
			"wpshadow_recovery_{$backup_id}",
			$backup,
			28 * DAY_IN_SECONDS
		);

		// Add to manifest
		self::add_to_manifest( $backup_id, $reason, $description );

		// Track KPI
		KPI_Tracker::record_action( 'recovery_point_created', 1 );

		return $backup_id;
	}

	/**
	 * Restore from recovery point
	 *
	 * Rolls back all captured options to backup state.
	 *
	 * @param string $backup_id Backup to restore
	 *
	 * @return array Result { success: bool, message: string }
	 */
	public static function restore_recovery_point( string $backup_id ): array {
		$backup_id = sanitize_key( $backup_id );

		// Get backup
		$backup = get_transient( "wpshadow_recovery_{$backup_id}" );
		if ( ! $backup ) {
			return array(
				'success' => false,
				'message' => 'Backup not found or expired',
			);
		}

		try {
			// Restore each option
			foreach ( $backup['snapshot'] as $option_name => $option_value ) {
				update_option( $option_name, $option_value );
			}

			// Log recovery
			Guardian_Activity_Logger::log_recovery( $backup_id, true );

			// Track KPI
			KPI_Tracker::record_action( 'recovery_applied', 1 );

			return array(
				'success' => true,
				'message' => 'Site restored to backup state',
			);
		} catch ( \Exception $e ) {
			Guardian_Activity_Logger::log_recovery( $backup_id, false, $e->getMessage() );

			return array(
				'success' => false,
				'message' => 'Recovery failed: ' . $e->getMessage(),
			);
		}
	}

	/**
	 * Get list of available recovery points
	 *
	 * @param int $limit Number to return
	 *
	 * @return array List of recovery points
	 */
	public static function get_recovery_points( int $limit = 20 ): array {
		$manifest = get_option( 'wpshadow_recovery_manifest', array() );
		return array_slice( array_reverse( $manifest ), 0, $limit );
	}

	/**
	 * Get recovery point details
	 *
	 * @param string $backup_id Backup to get
	 *
	 * @return array Backup details or null
	 */
	public static function get_recovery_point( string $backup_id ): ?array {
		$backup_id = sanitize_key( $backup_id );

		$backup = get_transient( "wpshadow_recovery_{$backup_id}" );
		if ( ! $backup ) {
			return null;
		}

		// Return without full snapshot for UI display
		return array(
			'id'            => $backup['id'],
			'timestamp'     => $backup['timestamp'],
			'reason'        => $backup['reason'],
			'description'   => $backup['description'],
			'options_count' => count( $backup['snapshot'] ?? array() ),
		);
	}

	/**
	 * Delete recovery point
	 *
	 * Removes backup after recovery or explicitly by user.
	 *
	 * @param string $backup_id Backup to delete
	 *
	 * @return bool Deleted successfully
	 */
	public static function delete_recovery_point( string $backup_id ): bool {
		$backup_id = sanitize_key( $backup_id );

		// Remove transient
		delete_transient( "wpshadow_recovery_{$backup_id}" );

		// Remove from manifest
		$manifest = get_option( 'wpshadow_recovery_manifest', array() );
		$manifest = array_filter(
			$manifest,
			fn( $m ) => $m['id'] !== $backup_id
		);
		update_option( 'wpshadow_recovery_manifest', array_values( $manifest ) );

		return true;
	}

	/**
	 * Clean up old recovery points
	 *
	 * Removes backups older than retention period.
	 * Called periodically to manage storage.
	 *
	 * @param int $days Retention days (default: 28)
	 */
	public static function cleanup_expired( int $days = 28 ): void {
		$manifest = get_option( 'wpshadow_recovery_manifest', array() );
		$cutoff   = time() - ( $days * DAY_IN_SECONDS );

		$manifest = array_filter(
			$manifest,
			function ( $entry ) use ( $cutoff ) {
				$entry_time = strtotime( $entry['timestamp'] );
				if ( $entry_time < $cutoff ) {
					// Delete transient
					delete_transient( "wpshadow_recovery_{$entry['id']}" );
					return false;
				}
				return true;
			}
		);

		update_option( 'wpshadow_recovery_manifest', array_values( $manifest ) );
	}

	/**
	 * Get recovery summary
	 *
	 * @return array Summary statistics
	 */
	public static function get_summary(): array {
		$manifest = get_option( 'wpshadow_recovery_manifest', array() );

		$stats = array(
			'total_points' => count( $manifest ),
			'oldest_point' => null,
			'newest_point' => null,
			'by_reason'    => array(),
		);

		if ( ! empty( $manifest ) ) {
			$stats['oldest_point'] = $manifest[0]['timestamp'] ?? null;
			$stats['newest_point'] = end( $manifest )['timestamp'] ?? null;

			// Group by reason
			foreach ( $manifest as $entry ) {
				$reason                        = $entry['reason'] ?? 'unknown';
				$stats['by_reason'][ $reason ] = ( $stats['by_reason'][ $reason ] ?? 0 ) + 1;
			}
		}

		return $stats;
	}

	/**
	 * Capture current site state snapshot
	 *
	 * Stores critical options for rollback.
	 *
	 * @return array Snapshot of option values
	 */
	private static function capture_snapshot(): array {
		$critical_options = array(
			'siteurl',
			'home',
			'admin_email',
			'blogname',
			'blogdescription',
			'active_plugins',
			'template',
			'stylesheet',
			'permalink_structure',
			'blog_public',
			'timezone_string',
		);

		$snapshot = array();

		foreach ( $critical_options as $option ) {
			$snapshot[ $option ] = get_option( $option );
		}

		return $snapshot;
	}

	/**
	 * Add to recovery manifest
	 *
	 * @param string $backup_id Backup ID
	 * @param string $reason Reason
	 * @param string $description Description
	 */
	private static function add_to_manifest( string $backup_id, string $reason, string $description ): void {
		$manifest = get_option( 'wpshadow_recovery_manifest', array() );

		$manifest[] = array(
			'id'          => $backup_id,
			'timestamp'   => current_time( 'mysql' ),
			'reason'      => $reason,
			'description' => $description,
		);

		// Keep last 100 for manifest, but transients hold full data
		$manifest = array_slice( $manifest, -100 );

		update_option( 'wpshadow_recovery_manifest', $manifest );
	}

	/**
	 * Render recovery points widget
	 *
	 * @return string HTML widget
	 */
	public static function render_recovery_widget(): string {
		$points = self::get_recovery_points( 5 );

		if ( empty( $points ) ) {
			return '<p>' . esc_html__( 'No recovery points yet', 'wpshadow' ) . '</p>';
		}

		ob_start();
		?>
		<div class="wpshadow-recovery-widget">
			<h3><?php esc_html_e( 'Recent Recovery Points', 'wpshadow' ); ?></h3>
			<ul>
				<?php foreach ( $points as $point ) : ?>
					<li>
						<strong><?php echo esc_html( $point['reason'] ); ?></strong>
						<br>
						<small><?php echo esc_html( $point['timestamp'] ); ?></small>
						<a href="#" class="button button-small" data-recovery-id="<?php echo esc_attr( $point['id'] ); ?>">
							<?php esc_html_e( 'Restore', 'wpshadow' ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php

		return ob_get_clean();
	}
}
