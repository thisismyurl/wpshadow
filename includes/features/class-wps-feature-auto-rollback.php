<?php
/**
 * Feature: Automatic Update Rollback
 *
 * Provides automatic rollback capability for core, theme, and plugin updates.
 * Creates snapshots before updates, validates afterward, and rolls back on failure.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

use WPS\CoreSupport\WPSHADOW_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Auto_Rollback
 *
 * Automatic rollback implementation with validation and diff summary.
 */
final class WPSHADOW_Feature_Auto_Rollback extends WPSHADOW_Abstract_Feature {

	/**
	 * Option key for storing pre-update snapshot ID.
	 */
	private const PRE_UPDATE_SNAPSHOT_KEY = 'wpshadow_pre_update_snapshot';

	/**
	 * Option key for tracking update in progress.
	 */
	private const UPDATE_IN_PROGRESS_KEY = 'wpshadow_update_in_progress';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'auto-rollback',
				'name'               => __( 'Automatic Update Rollback', 'plugin-wpshadow' ),
				'description'        => __( 'Automatically create snapshots before updates (core/theme/plugins), validate after completion, and rollback on failure with diff summary', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'advanced',
				'widget_label'       => __( 'Safety Features', 'plugin-wpshadow' ),
				'widget_description' => __( 'Advanced safety and recovery features to protect your WordPress installation', 'plugin-wpshadow' ),
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Hook before any update starts.
		add_filter( 'upgrader_pre_install', array( $this, 'before_update' ), 10, 2 );

		// Hook after update completes.
		add_filter( 'upgrader_post_install', array( $this, 'after_update' ), 10, 3 );

		// Hook after the entire upgrade process completes.
		add_action( 'upgrader_process_complete', array( $this, 'validate_and_rollback' ), 999, 2 );

		// Admin notice for rollback results.
		add_action( 'admin_notices', array( $this, 'display_rollback_notice' ) );
	}

	/**
	 * Create snapshot before update starts.
	 *
	 * @param bool  $response   Installation response.
	 * @param array $hook_extra Extra arguments passed to hooked filters.
	 * @return bool Always returns $response to allow update to proceed.
	 */
	public function before_update( $response, $hook_extra ): bool {
		// Only create snapshot if we're doing an update (not install).
		if ( isset( $hook_extra['action'] ) && 'update' === $hook_extra['action'] ) {
			$type = $hook_extra['type'] ?? 'unknown';

			// Create snapshot description.
			$description = sprintf(
				'Pre-update snapshot: %s update',
				ucfirst( $type )
			);

			// Create the snapshot.
			$snapshot_id = WPSHADOW_Snapshot_Manager::create_snapshot( $description );

			if ( $snapshot_id ) {
				// Store snapshot ID for later validation.
				$this->update_setting( self::PRE_UPDATE_SNAPSHOT_KEY, $snapshot_id, false  );
				$this->update_setting( self::UPDATE_IN_PROGRESS_KEY, array(
						'type'      => $type,
						'timestamp' => time( ),
					),
					false
				);

			}
		}

		return $response;
	}

	/**
	 * Hook after files are installed but before activation.
	 *
	 * @param bool  $response   Installation response.
	 * @param array $hook_extra Extra arguments passed to hooked filters.
	 * @param array $result     Installation result.
	 * @return bool Installation response.
	 */
	public function after_update( $response, $hook_extra, $result ) {
		// This is called after files are installed but before plugin/theme activation.
		// We'll do the main validation in validate_and_rollback() after everything completes.
		return $response;
	}

	/**
	 * Validate update and rollback if necessary.
	 *
	 * @param object $upgrader Upgrader instance.
	 * @param array  $options  Upgrader options.
	 * @return void
	 */
	public function validate_and_rollback( $upgrader, $options ): void {
		// Check if we have a pre-update snapshot.
		$pre_snapshot_id = $this->get_setting( self::PRE_UPDATE_SNAPSHOT_KEY );
		$update_info     = get_option( self::UPDATE_IN_PROGRESS_KEY );

		if ( ! $pre_snapshot_id || ! $update_info ) {
			return;
		}

		// Perform validation checks.
		$validation_result = $this->validate_site_health();

		if ( $validation_result['success'] ) {
			// Update succeeded - create post-update snapshot for comparison.
			$post_snapshot_id = WPSHADOW_Snapshot_Manager::create_snapshot(
				sprintf(
					'Post-update snapshot: %s update succeeded', $update_info['type']
				 )
			);

			// Generate diff summary.
			if ( $post_snapshot_id ) {
				$diff = $this->generate_diff_summary( $pre_snapshot_id, $post_snapshot_id );

				// Store success notice with diff.
				set_transient(
					'wpshadow_rollback_notice',
					array(
						'type'    => 'success',
						'message' => sprintf(
							__( 'Update completed successfully. %s', 'plugin-wpshadow' ),
							$diff
						),
					),
					300
				);

			}
		} else {
			// Validation failed - perform rollback.
			$this->perform_rollback( $pre_snapshot_id, $validation_result['errors'], $update_info['type'] );
		}

		// Clean up tracking options.
		delete_option( self::PRE_UPDATE_SNAPSHOT_KEY );
		delete_option( self::UPDATE_IN_PROGRESS_KEY );
	}

	/**
	 * Validate site health after update.
	 *
	 * @return array Validation result with 'success' and 'errors' keys.
	 */
	private function validate_site_health(): array {
		$errors = array();

		// Check 1: PHP fatal error detection.
		$error_log = $this->get_recent_php_errors();
		if ( ! empty( $error_log ) ) {
			$errors[] = __( 'PHP errors detected after update', 'plugin-wpshadow' );
		}

		// Check 2: WordPress loading properly.
		if ( ! did_action( 'plugins_loaded' ) ) {
			$errors[] = __( 'WordPress plugins failed to load', 'plugin-wpshadow' );
		}

		// Check 3: Database connection.
		global $wpdb;
		if ( ! empty( $wpdb->last_error ) ) {
			$errors[] = __( 'Database errors detected', 'plugin-wpshadow' );
		}

		// Check 4: Critical WordPress functionality.
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			$errors[] = __( 'Critical WordPress functions unavailable', 'plugin-wpshadow' );
		}

		return array(
			'success' => empty( $errors ),
			'errors'  => $errors,
		);
	}

	/**
	 * Get recent PHP errors from error log.
	 *
	 * @return array Recent error messages.
	 */
	private function get_recent_php_errors(): array {
		$errors = array();

		// Check if we can access error log.
		$error_log_file = ini_get( 'error_log' );
		if ( empty( $error_log_file ) || ! file_exists( $error_log_file ) || ! is_readable( $error_log_file ) ) {
			return $errors;
		}

		// Read last 50 lines of error log.
		$lines = $this->tail_file( $error_log_file, 50 );

		// Filter for recent errors (last 5 minutes).
		$time_threshold = time() - 300;

		foreach ( $lines as $line ) {
			// Look for PHP fatal errors, warnings, or notices in recent timeframe.
			if ( preg_match( '/\[(\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2}.*?)\]/', $line, $matches ) ) {
				$error_time = strtotime( $matches[1] );
				if ( $error_time && $error_time > $time_threshold ) {
					if ( stripos( $line, 'Fatal error' ) !== false ||
						stripos( $line, 'Parse error' ) !== false ||
						stripos( $line, 'Catchable fatal error' ) !== false ) {
						$errors[] = $line;
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Read last N lines from a file.
	 *
	 * @param string $file Path to file.
	 * @param int    $lines Number of lines to read.
	 * @return array Lines from file.
	 */
	private function tail_file( string $file, int $lines = 50 ): array {
		// Check if file exists and is readable.
		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return array();
		}

		// Get file size.
		$file_size = filesize( $file );
		if ( false === $file_size || 0 === $file_size ) {
			return array();
		}

		// Use file() function which is more efficient and handles edge cases.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$all_lines = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

		if ( false === $all_lines ) {
			return array();
		}

		// Return last N lines.
		return array_slice( $all_lines, -$lines );
	}

	/**
	 * Perform rollback to pre-update snapshot.
	 *
	 * @param string $snapshot_id Snapshot ID to restore.
	 * @param array  $errors      Validation errors.
	 * @param string $update_type Type of update that failed.
	 * @return void
	 */
	private function perform_rollback( string $snapshot_id, array $errors, string $update_type ): void {

		// Attempt to restore the snapshot.
		$restored = WPSHADOW_Snapshot_Manager::restore_snapshot( $snapshot_id );

		if ( $restored ) {
			// Store rollback notice.
			set_transient(
				'wpshadow_rollback_notice',
				array(
					'type'    => 'error',
					'message' => sprintf(
						__( 'Update failed and was automatically rolled back. Errors: %s', 'plugin-wpshadow' ),
						implode( ', ', $errors )
					),
				),
				300
			);

		} else {
			// Rollback failed.
			set_transient(
				'wpshadow_rollback_notice',
				array(
					'type'    => 'error',
					'message' => __( 'Update failed and automatic rollback also failed. Please restore manually.', 'plugin-wpshadow' ),
				),
				300
			);
		}
	}

	/**
	 * Generate diff summary between two snapshots.
	 *
	 * @param string $snapshot_id_1 First snapshot ID (before).
	 * @param string $snapshot_id_2 Second snapshot ID (after).
	 * @return string Formatted diff summary.
	 */
	private function generate_diff_summary( string $snapshot_id_1, string $snapshot_id_2 ): string {
		$comparison = WPSHADOW_Snapshot_Manager::compare_snapshots( $snapshot_id_1, $snapshot_id_2 );

		if ( empty( $comparison ) ) {
			return __( 'No changes detected.', 'plugin-wpshadow' );
		}

		$summary = array();

		// WordPress version change.
		if ( ! empty( $comparison['versions']['wordpress']['changed'] ) ) {
			$summary[] = sprintf(
				__( 'WordPress: %1$s → %2$s', 'plugin-wpshadow' ),
				$comparison['versions']['wordpress']['before'],
				$comparison['versions']['wordpress']['after']
			);
		}

		// Plugin changes.
		if ( ! empty( $comparison['plugins'] ) ) {
			$plugin_summary = array();

			if ( ! empty( $comparison['plugins']['updated'] ) ) {
				$count            = count( $comparison['plugins']['updated'] );
				$plugin_summary[] = sprintf(
					_n( '%d plugin updated', '%d plugins updated', $count, 'plugin-wpshadow' ),
					$count
				);
			}

			if ( ! empty( $comparison['plugins']['added'] ) ) {
				$count            = count( $comparison['plugins']['added'] );
				$plugin_summary[] = sprintf(
					_n( '%d plugin added', '%d plugins added', $count, 'plugin-wpshadow' ),
					$count
				);
			}

			if ( ! empty( $comparison['plugins']['removed'] ) ) {
				$count            = count( $comparison['plugins']['removed'] );
				$plugin_summary[] = sprintf(
					_n( '%d plugin removed', '%d plugins removed', $count, 'plugin-wpshadow' ),
					$count
				);
			}

			if ( ! empty( $comparison['plugins']['activated'] ) ) {
				$count            = count( $comparison['plugins']['activated'] );
				$plugin_summary[] = sprintf(
					_n( '%d plugin activated', '%d plugins activated', $count, 'plugin-wpshadow' ),
					$count
				);
			}

			if ( ! empty( $comparison['plugins']['deactivated'] ) ) {
				$count            = count( $comparison['plugins']['deactivated'] );
				$plugin_summary[] = sprintf(
					_n( '%d plugin deactivated', '%d plugins deactivated', $count, 'plugin-wpshadow' ),
					$count
				);
			}

			if ( ! empty( $plugin_summary ) ) {
				$summary[] = implode( ', ', $plugin_summary );
			}
		}

		// Theme change.
		if ( ! empty( $comparison['theme']['changed'] ) ) {
			$summary[] = sprintf(
				__( 'Theme: %1$s → %2$s', 'plugin-wpshadow' ),
				$comparison['theme']['before'],
				$comparison['theme']['after']
			);
		}

		return ! empty( $summary ) ? implode( '. ', $summary ) . '.' : __( 'No significant changes detected.', 'plugin-wpshadow' );
	}

	/**
	 * Display admin notice for rollback results.
	 *
	 * @return void
	 */
	public function display_rollback_notice(): void {
		$notice = get_transient( 'wpshadow_rollback_notice' );

		if ( ! $notice || ! is_array( $notice ) ) {
			return;
		}

		$type    = $notice['type'] ?? 'info';
		$message = $notice['message'] ?? '';

		if ( empty( $message ) ) {
			return;
		}

		// Delete transient so notice only shows once.
		delete_transient( 'wpshadow_rollback_notice' );

		$class = 'success' === $type ? 'notice-success' : 'notice-error';

		printf(
			'<div class="notice %s is-dismissible"><p><strong>%s:</strong> %s</p></div>',
			esc_attr( $class ),
			esc_html__( 'Automatic Rollback', 'plugin-wpshadow' ),
			esc_html( $message )
		);
	}
}
