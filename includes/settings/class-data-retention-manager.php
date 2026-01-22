<?php
declare(strict_types=1);

namespace WPShadow\Settings;

/**
 * Data Retention Manager
 *
 * Manages activity log retention policies and automatic cleanup.
 * Philosophy: Beyond Pure (#10) - User control over data lifecycle
 * Philosophy: Show Value (#9) - Maintain valuable historical data
 *
 * @since 1.2601
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
		$finding_log = get_option( 'wpshadow_finding_log', array() );

		if ( empty( $finding_log ) ) {
			return 0;
		}

		$cutoff_time    = strtotime( "-{$days_to_keep} days" );
		$original_count = count( $finding_log );

		$finding_log = array_filter(
			$finding_log,
			function ( $entry ) use ( $cutoff_time ) {
				$timestamp = isset( $entry['timestamp'] ) ? $entry['timestamp'] : 0;
				return $timestamp > $cutoff_time;
			}
		);

		update_option( 'wpshadow_finding_log', $finding_log );

		return $original_count - count( $finding_log );
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
		$settings = self::get_retention_settings();
		?>
		<div style="max-width: 800px;">
			<!-- Retention Policies -->
			<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px; margin-bottom: 20px;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
					<span class="dashicons dashicons-database" style="font-size: 24px; color: #0073aa;"></span>
					<h3 style="margin: 0;"><?php esc_html_e( 'Data Retention Policies', 'wpshadow' ); ?></h3>
				</div>
				<p style="color: #666; margin: 0 0 16px 0;">
					<?php esc_html_e( 'Configure how long WPShadow keeps historical records and logs.', 'wpshadow' ); ?>
				</p>
				
				<form class="wpshadow-retention-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php wp_nonce_field( 'wpshadow_retention_settings_nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_update_retention_settings" />
					
					<!-- Activity Log Retention -->
					<div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
						<label style="display: block; margin-bottom: 8px; font-weight: 500;">
							<?php esc_html_e( 'Activity Log Retention (days):', 'wpshadow' ); ?>
						</label>
						<input type="number" name="activity_log_days" value="<?php echo esc_attr( $settings['activity_log_days'] ); ?>" min="7" max="730" style="width: 100%; max-width: 200px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
						<p style="font-size: 12px; color: #666; margin: 6px 0 0 0;">
							<?php esc_html_e( 'How long to keep workflow runs, diagnostics, and user actions (7-730 days)', 'wpshadow' ); ?>
						</p>
					</div>
					
					<!-- Finding Log Retention -->
					<div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
						<label style="display: block; margin-bottom: 8px; font-weight: 500;">
							<?php esc_html_e( 'Finding Log Retention (days):', 'wpshadow' ); ?>
						</label>
						<input type="number" name="finding_log_days" value="<?php echo esc_attr( $settings['finding_log_days'] ); ?>" min="7" max="730" style="width: 100%; max-width: 200px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
						<p style="font-size: 12px; color: #666; margin: 6px 0 0 0;">
							<?php esc_html_e( 'How long to keep records of detected site issues and resolutions (7-730 days)', 'wpshadow' ); ?>
						</p>
					</div>
					
					<!-- Workflow Log Retention -->
					<div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
						<label style="display: block; margin-bottom: 8px; font-weight: 500;">
							<?php esc_html_e( 'Workflow Log Retention (days):', 'wpshadow' ); ?>
						</label>
						<input type="number" name="workflow_log_days" value="<?php echo esc_attr( $settings['workflow_log_days'] ); ?>" min="7" max="730" style="width: 100%; max-width: 200px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
						<p style="font-size: 12px; color: #666; margin: 6px 0 0 0;">
							<?php esc_html_e( 'How long to keep records of scheduled workflow executions (7-730 days)', 'wpshadow' ); ?>
						</p>
					</div>
					
					<!-- Auto Cleanup -->
					<fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 4px;">
						<legend style="font-weight: 500; padding: 0 10px;"><?php esc_html_e( 'Automatic Cleanup', 'wpshadow' ); ?></legend>
						
						<div style="margin-top: 12px; display: flex; align-items: flex-start; gap: 12px;">
							<input type="checkbox" name="auto_cleanup_enabled" <?php checked( $settings['auto_cleanup_enabled'] ); ?> id="auto-cleanup-enabled" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
							<div style="flex: 1;">
								<label for="auto-cleanup-enabled" style="cursor: pointer; font-weight: 500; display: block;">
									<?php esc_html_e( 'Enable automatic cleanup', 'wpshadow' ); ?>
								</label>
								<p style="font-size: 12px; color: #666; margin: 2px 0 0 0;">
									<?php esc_html_e( 'Automatically delete old records according to your retention settings.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
						
						<div style="margin-top: 12px;">
							<label style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 500;">
								<?php esc_html_e( 'Cleanup Time:', 'wpshadow' ); ?>
							</label>
							<input type="time" name="cleanup_time" value="<?php echo esc_attr( $settings['cleanup_time'] ); ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
							<p style="font-size: 12px; color: #666; margin: 4px 0 0 0;">
								<?php esc_html_e( 'Time of day to run automatic cleanup (runs daily)', 'wpshadow' ); ?>
							</p>
						</div>
					</fieldset>
					
					<!-- Save Button -->
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save Retention Settings', 'wpshadow' ); ?>
					</button>
					<span id="wpshadow-retention-status" style="margin-left: 10px;"></span>
				</form>
			</div>

			<!-- Manual Cleanup -->
			<div style="background: #fff3e0; border: 1px solid #ff9800; border-radius: 8px; padding: 16px;">
				<div style="display: flex; align-items: center; gap: 12px;">
					<span class="dashicons dashicons-update" style="font-size: 24px; color: #e65100;"></span>
					<div style="flex: 1;">
						<strong style="color: #e65100;"><?php esc_html_e( 'Run Cleanup Now', 'wpshadow' ); ?></strong>
						<p style="font-size: 12px; color: #666; margin: 4px 0 0 0;">
							<?php esc_html_e( 'Manually trigger data cleanup to remove old logs immediately.', 'wpshadow' ); ?>
						</p>
					</div>
					<button type="button" id="wpshadow-cleanup-now-btn" class="button" style="flex-shrink: 0;">
						<?php esc_html_e( 'Run Now', 'wpshadow' ); ?>
					</button>
				</div>
				<div id="wpshadow-cleanup-result" style="margin-top: 12px;"></div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Save retention settings
			$('.wpshadow-retention-form').on('submit', function(e) {
				e.preventDefault();
				var $form = $(this);
				var $btn = $form.find('button[type="submit"]');
				var $status = $('#wpshadow-retention-status');
				
				var data = {
					action: 'wpshadow_update_retention_settings',
					nonce: $form.find('input[name="_wpnonce"]').val(),
					activity_log_days: $form.find('input[name="activity_log_days"]').val(),
					finding_log_days: $form.find('input[name="finding_log_days"]').val(),
					workflow_log_days: $form.find('input[name="workflow_log_days"]').val(),
					auto_cleanup_enabled: $form.find('input[name="auto_cleanup_enabled"]').prop('checked'),
					cleanup_time: $form.find('input[name="cleanup_time"]').val(),
				};
				
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
				$status.html('');
				
				$.post(ajaxurl, data, function(response) {
					if (response.success) {
						$status.html('<span style="color: #2e7d32;">✓ <?php echo esc_js( __( 'Saved', 'wpshadow' ) ); ?></span>');
					} else {
						$status.html('<span style="color: #c62828;">✗ ' + (response.data.message || '<?php echo esc_js( __( 'Error', 'wpshadow' ) ); ?>') + '</span>');
					}
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Retention Settings', 'wpshadow' ) ); ?>');
				});
			});
			
			// Run cleanup now
			$('#wpshadow-cleanup-now-btn').on('click', function() {
				var $btn = $(this);
				var $result = $('#wpshadow-cleanup-result');
				
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Running...', 'wpshadow' ) ); ?>');
				$result.html('');
				
				$.post(ajaxurl, {
					action: 'wpshadow_run_data_cleanup_now',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_retention_settings_nonce' ); ?>'
				}, function(response) {
					if (response.success && response.data.results) {
						var results = response.data.results;
						var html = '<div style="padding: 12px; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">' +
							'<strong>✓ ' + response.data.message + '</strong><br/>' +
							'Activity logs: ' + results.activity_logs + ' removed<br/>' +
							'Finding logs: ' + results.finding_logs + ' removed<br/>' +
							'Workflow logs: ' + results.workflow_logs + ' removed' +
							'</div>';
						$result.html(html);
					} else {
						$result.html('<div style="padding: 12px; background: #ffebee; color: #c62828; border-radius: 4px;">✗ ' + (response.data.message || '<?php echo esc_js( __( 'Error running cleanup', 'wpshadow' ) ); ?>') + '</div>');
					}
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run Now', 'wpshadow' ) ); ?>');
				});
			});
		});
		</script>
		<?php
	}
}
