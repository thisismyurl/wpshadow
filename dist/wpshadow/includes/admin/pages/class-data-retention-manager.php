<?php
declare(strict_types=1);

namespace WPShadow\Admin\Pages;

/**
 * Data Retention Manager
 *
 * Manages activity log retention policies and automatic cleanup.
 * Philosophy: Beyond Pure (#10) - User control over data lifecycle
 * Philosophy: Show Value (#9) - Maintain valuable historical data
 *
 * @since 0.6093.1200
 * @package WPShadow
 */
class Data_Retention_Manager {

	/**
	 * Option key for retention settings
	 */
	const OPTION_KEY = 'wpshadow_data_retention_settings';

	/**
	 * Get all retention settings
	 *
	 * @return array Retention settings
	 */
	public static function get_retention_settings() {
		return get_option(
			self::OPTION_KEY,
			array(
				'activity_log_days'    => 90,
				'finding_log_days'     => 180,
				'workflow_log_days'    => 60,
				'auto_cleanup_enabled' => true,
				'cleanup_time'         => '03:00', // 3 AM
			)
		);
	}

	/**
	 * Update retention setting
	 *
	 * @param string $key Setting key
	 * @param mixed  $value Setting value
	 * @return bool Success status
	 */
	public static function update_setting( $key, $value ) {
		if ( empty( $key ) ) {
			return false;
		}

		$settings         = self::get_retention_settings();
		$settings[ $key ] = $value;

		$result = update_option( self::OPTION_KEY, $settings );

		// Schedule or reschedule cron if cleanup enabled
		if ( $key === 'auto_cleanup_enabled' || $key === 'cleanup_time' ) {
			if ( $settings['auto_cleanup_enabled'] ) {
				self::schedule_cleanup_cron();
			} else {
				wp_clear_scheduled_hook( 'wpshadow_run_data_cleanup' );
			}
		}

		// Log activity
		if ( $result && class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'retention_setting_updated',
				sprintf( 'Data retention setting updated: %s', $key ),
				'',
				array(
					'setting_key' => $key,
					'value'       => $value,
				)
			);
		}

		return $result;
	}

	/**
	 * Schedule data cleanup cron job
	 *
	 * @return void
	 */
	private static function schedule_cleanup_cron() {
		$settings     = self::get_retention_settings();
		$cleanup_time = $settings['cleanup_time'] ?? '03:00';

		// Unschedule existing
		wp_clear_scheduled_hook( 'wpshadow_run_data_cleanup' );

		// Parse time (format: HH:MM)
		list( $hour, $minute ) = explode( ':', $cleanup_time );
		$hour                  = (int) $hour;
		$minute                = (int) $minute;

		// Calculate next run
		$now        = time();
		$today_time = mktime( $hour, $minute, 0 );

		if ( $today_time > $now ) {
			$next_run = $today_time;
		} else {
			$next_run = $today_time + DAY_IN_SECONDS;
		}

		// Schedule daily event
		wp_schedule_event( $next_run, 'daily', 'wpshadow_run_data_cleanup' );
	}

	/**
	 * Run data cleanup (removes old activity entries)
	 *
	 * @return array Cleanup results
	 */
	public static function run_cleanup() {
		$settings = self::get_retention_settings();
		$results  = array();

		// Clean activity logs
		$activity_deleted         = self::cleanup_activity_logs( $settings['activity_log_days'] ?? 90 );
		$results['activity_logs'] = $activity_deleted;

		// Clean finding logs
		$finding_deleted         = self::cleanup_finding_logs( $settings['finding_log_days'] ?? 180 );
		$results['finding_logs'] = $finding_deleted;

		// Clean workflow logs
		$workflow_deleted         = self::cleanup_workflow_logs( $settings['workflow_log_days'] ?? 60 );
		$results['workflow_logs'] = $workflow_deleted;

		// Log cleanup event
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'data_cleanup_completed',
				sprintf(
					'Data cleanup completed: %d activity, %d finding, %d workflow records removed',
					$activity_deleted,
					$finding_deleted,
					$workflow_deleted
				),
				'',
				array( 'results' => $results )
			);
		}

		return $results;
	}

	/**
	 * Clean old activity log entries
	 *
	 * @param int $days_to_keep Number of days to keep
	 * @return int Number of records deleted
	 */
	private static function cleanup_activity_logs( $days_to_keep ) {
		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			return 0;
		}

		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_to_keep} days" ) );

		return \WPShadow\Core\Activity_Logger::delete_old_entries( $cutoff_date );
	}

	/**
	 * Clean old finding log entries
	 *
	 * @param int $days_to_keep Number of days to keep
	 * @return int Number of records deleted
	 */
	private static function cleanup_finding_logs( $days_to_keep ) {
		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			return 0;
		}

		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_to_keep} days" ) );

		return \WPShadow\Core\Activity_Logger::delete_old_entries( $cutoff_date );
	}

	/**
	 * Clean old workflow log entries
	 *
	 * @param int $days_to_keep Number of days to keep
	 * @return int Number of records deleted
	 */
	private static function cleanup_workflow_logs( $days_to_keep ) {
		$workflow_history = get_option( 'wpshadow_workflow_execution_history', array() );

		if ( empty( $workflow_history ) ) {
			return 0;
		}

		$cutoff_time    = strtotime( "-{$days_to_keep} days" );
		$original_count = count( $workflow_history );

		$workflow_history = array_filter(
			$workflow_history,
			function ( $entry ) use ( $cutoff_time ) {
				$timestamp = isset( $entry['timestamp'] ) ? $entry['timestamp'] : 0;
				return $timestamp > $cutoff_time;
			}
		);

		update_option( 'wpshadow_workflow_execution_history', $workflow_history );

		return $original_count - count( $workflow_history );
	}

	/**
	 * Render data retention UI
	 *
	 * @return void
	 */
	public static function render_retention_ui() {
		$version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0';

		wp_enqueue_script(
			'wpshadow-data-retention-manager',
			WPSHADOW_URL . 'assets/js/data-retention-manager.js',
			array( 'jquery' ),
			$version,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-data-retention-manager',
			'wpsDataRetentionManager',
			'wpshadow_retention_settings_nonce',
			array(
				'cleanupNonce'        => wp_create_nonce( 'wpshadow_retention_settings_nonce' ),
				'savingText'          => __( 'Saving...', 'wpshadow' ),
				'savedText'           => __( 'Saved', 'wpshadow' ),
				'errorText'           => __( 'Error', 'wpshadow' ),
				'saveButtonText'      => __( 'Save Retention Settings', 'wpshadow' ),
				'runningText'         => __( 'Running...', 'wpshadow' ),
				'runNowText'          => __( 'Run Now', 'wpshadow' ),
				'cleanupErrorText'    => __( 'Error running cleanup', 'wpshadow' ),
			)
		);

		$settings = self::get_retention_settings();
		?>
		<div class="wps-retention-container">
			<!-- Retention Policies -->
			<div class="wps-p-24-rounded-8">
				<div class="wps-flex-gap-12-items-center">
					<span class="dashicons dashicons-database" class="wps-retention-icon"></span>
					<h3 class="wps-m-0"><?php esc_html_e( 'Data Retention Policies', 'wpshadow' ); ?></h3>
				</div>
				<p class="wps-m-0">
					<?php esc_html_e( 'Configure how long WPShadow keeps historical records and logs.', 'wpshadow' ); ?>
				</p>

				<form class="wpshadow-retention-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php wp_nonce_field( 'wpshadow_retention_settings_nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_update_retention_settings" />

					<!-- Activity Log Retention -->
					<div class="wps-p-15-rounded-4">
						<div class="wps-range-group">
							<div class="wps-range-header">
								<label class="wps-label" for="activity_log_days">
									<?php esc_html_e( 'Activity Log Retention (days):', 'wpshadow' ); ?>
								</label>
								<span class="wps-range-value" id="activity_log_days_display"><?php echo esc_html( $settings['activity_log_days'] ); ?> days</span>
							</div>
							<div class="wps-range-wrapper">
								<input
									type="range"
									id="activity_log_days"
									name="activity_log_days"
									class="wps-range"
									min="7"
									max="730"
									value="<?php echo esc_attr( $settings['activity_log_days'] ); ?>"
									step="1"
									data-suffix=" days"
									aria-valuemin="7"
									aria-valuemax="730"
									aria-valuenow="<?php echo esc_attr( $settings['activity_log_days'] ); ?>"
									aria-valuetext="<?php echo esc_attr( $settings['activity_log_days'] ); ?> days"
								/>
							</div>
							<span class="wps-help-text">
								<?php esc_html_e( 'How long to keep workflow runs, diagnostics, and user actions (7-730 days)', 'wpshadow' ); ?>
							</span>
						</div>
					</div>

					<!-- Finding Log Retention -->
					<div class="wps-p-15-rounded-4">
						<div class="wps-range-group">
							<div class="wps-range-header">
								<label class="wps-label" for="finding_log_days">
									<?php esc_html_e( 'Finding Log Retention (days):', 'wpshadow' ); ?>
								</label>
								<span class="wps-range-value" id="finding_log_days_display"><?php echo esc_html( $settings['finding_log_days'] ); ?> days</span>
							</div>
							<div class="wps-range-wrapper">
								<input
									type="range"
									id="finding_log_days"
									name="finding_log_days"
									class="wps-range"
									min="7"
									max="730"
									value="<?php echo esc_attr( $settings['finding_log_days'] ); ?>"
									step="1"
									data-suffix=" days"
									aria-valuemin="7"
									aria-valuemax="730"
									aria-valuenow="<?php echo esc_attr( $settings['finding_log_days'] ); ?>"
									aria-valuetext="<?php echo esc_attr( $settings['finding_log_days'] ); ?> days"
								/>
							</div>
							<span class="wps-help-text">
								<?php esc_html_e( 'How long to keep records of detected site issues and resolutions (7-730 days)', 'wpshadow' ); ?>
							</span>
						</div>
					</div>

					<!-- Workflow Log Retention -->
					<div class="wps-p-15-rounded-4">
						<div class="wps-range-group">
							<div class="wps-range-header">
								<label class="wps-label" for="workflow_log_days">
									<?php esc_html_e( 'Workflow Log Retention (days):', 'wpshadow' ); ?>
								</label>
								<span class="wps-range-value" id="workflow_log_days_display"><?php echo esc_html( $settings['workflow_log_days'] ); ?> days</span>
							</div>
							<div class="wps-range-wrapper">
								<input
									type="range"
									id="workflow_log_days"
									name="workflow_log_days"
									class="wps-range"
									min="7"
									max="730"
									value="<?php echo esc_attr( $settings['workflow_log_days'] ); ?>"
									step="1"
									data-suffix=" days"
									aria-valuemin="7"
									aria-valuemax="730"
									aria-valuenow="<?php echo esc_attr( $settings['workflow_log_days'] ); ?>"
									aria-valuetext="<?php echo esc_attr( $settings['workflow_log_days'] ); ?> days"
								/>
							</div>
							<span class="wps-help-text">
								<?php esc_html_e( 'How long to keep records of scheduled workflow executions (7-730 days)', 'wpshadow' ); ?>
							</span>
						</div>
					</div>

					<!-- Auto Cleanup -->
					<fieldset class="wps-p-15-rounded-4">
						<legend class="wps-p-0"><?php esc_html_e( 'Automatic Cleanup', 'wpshadow' ); ?></legend>

						<div class="wps-flex-gap-12-items-flex-start">
							<label class="wps-toggle" for="auto-cleanup-enabled">
								<input type="checkbox" name="auto_cleanup_enabled" <?php checked( $settings['auto_cleanup_enabled'] ); ?> id="auto-cleanup-enabled" value="1" />
								<span class="wps-toggle-slider"></span>
								<?php esc_html_e( 'Enable automatic cleanup', 'wpshadow' ); ?>
							</label>
							<div class="wps-retention-flex-item">
								<p class="wps-m-2">
									<?php esc_html_e( 'Automatically delete old records according to your retention settings.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>

						<div class="wps-retention-margin-top">
							<label class="wps-block">
								<?php esc_html_e( 'Cleanup Time:', 'wpshadow' ); ?>
							</label>
							<input type="time" name="cleanup_time" value="<?php echo esc_attr( $settings['cleanup_time'] ); ?>" class="wps-p-8-rounded-4" />
							<p class="wps-m-4">
								<?php esc_html_e( 'Time of day to run automatic cleanup (runs daily)', 'wpshadow' ); ?>
							</p>
						</div>
					</fieldset>

					<!-- Save Button -->
					<button type="submit" class="wps-btn wps-btn-primary">
						<?php esc_html_e( 'Save Retention Settings', 'wpshadow' ); ?>
					</button>
					<span id="wpshadow-retention-status" class="wps-retention-status"></span>
				</form>
			</div>

			<!-- Manual Cleanup -->
			<div class="wps-p-16-rounded-8">
				<div class="wps-flex-gap-12-items-center">
					<span class="dashicons dashicons-update" class="wps-retention-action-icon"></span>
					<div class="wps-retention-flex-item">
						<strong class="wps-retention-action-label"><?php esc_html_e( 'Run Cleanup Now', 'wpshadow' ); ?></strong>
						<p class="wps-m-4">
							<?php esc_html_e( 'Manually trigger data cleanup to remove old logs immediately.', 'wpshadow' ); ?>
						</p>
					</div>
					<button type="button" id="wpshadow-cleanup-now-btn" class="button" class="wps-retention-button">
						<?php esc_html_e( 'Run Now', 'wpshadow' ); ?>
					</button>
				</div>
				<div id="wpshadow-cleanup-result" class="wps-retention-margin-top"></div>
			</div>
		</div>

		<?php
	}
}
