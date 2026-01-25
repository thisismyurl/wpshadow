<?php
declare(strict_types=1);

namespace WPShadow\Settings;

/**
 * Scan Frequency Manager
 *
 * Manages diagnostic scan scheduling and frequency configuration.
 * Philosophy: Helpful Neighbor (#1) - Let users choose their own schedule
 * Philosophy: Show Value (#9) - Track scan results and improvements
 *
 * @since 1.2601
 * @package WPShadow
 */
class Scan_Frequency_Manager {

	/**
	 * Option key for scan frequency settings
	 */
	const OPTION_KEY = 'wpshadow_scan_frequency_settings';

	/**
	 * Get available scan frequencies
	 *
	 * @return array Frequency options with descriptions
	 */
	public static function get_available_frequencies() {
		return array(
			'manual' => array(
				'label'       => __( 'Manual Only', 'wpshadow' ),
				'description' => __( 'Run scans only when you click the button', 'wpshadow' ),
				'icon'        => 'dashicons-admin-generic',
			),
			'hourly' => array(
				'label'       => __( 'Hourly', 'wpshadow' ),
				'description' => __( 'Run scans every hour (every 60 minutes)', 'wpshadow' ),
				'icon'        => 'dashicons-update',
			),
			'daily'  => array(
				'label'       => __( 'Daily', 'wpshadow' ),
				'description' => __( 'Run scans once per day (recommended)', 'wpshadow' ),
				'icon'        => 'dashicons-calendar-alt',
			),
			'weekly' => array(
				'label'       => __( 'Weekly', 'wpshadow' ),
				'description' => __( 'Run scans once per week (good for low-traffic sites)', 'wpshadow' ),
				'icon'        => 'dashicons-calendar',
			),
		);
	}

	/**
	 * Get current scan frequency configuration
	 *
	 * @return array Scan frequency settings
	 */
	public static function get_scan_config() {
		return get_option(
			self::OPTION_KEY,
			array(
				'frequency'             => 'daily',
				'scan_time'             => '02:00', // 2 AM
				'run_diagnostics'       => true,
				'run_treatments'        => false,
				'email_results'         => false,
				'scan_on_plugin_update' => true,
				'scan_on_theme_update'  => true,
			)
		);
	}

	/**
	 * Update scan frequency setting
	 *
	 * @param string $key Setting key
	 * @param mixed  $value Setting value
	 * @return bool Success status
	 */
	public static function update_setting( $key, $value ) {
		if ( empty( $key ) ) {
			return false;
		}

		$config         = self::get_scan_config();
		$old_value      = $config[ $key ] ?? null;
		$config[ $key ] = $value;

		// Validate frequency
		if ( $key === 'frequency' ) {
			$available = self::get_available_frequencies();
			if ( ! isset( $available[ $value ] ) && $value !== 'manual' ) {
				return false;
			}
		}

		$result = update_option( self::OPTION_KEY, $config );

		// Schedule or reschedule cron if frequency changed
		if ( $key === 'frequency' || $key === 'scan_time' ) {
			if ( $config['frequency'] !== 'manual' ) {
				self::schedule_scan_cron();
			} else {
				wp_clear_scheduled_hook( 'wpshadow_run_automatic_diagnostic_scan' );
			}
		}

		// Log activity
		if ( $result && class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'scan_frequency_updated',
				sprintf( 'Scan frequency setting changed: %s from "%s" to "%s"', $key, $old_value, $value ),
				'',
				array(
					'setting_key' => $key,
					'old_value'   => $old_value,
					'new_value'   => $value,
				)
			);
		}

		return $result;
	}

	/**
	 * Schedule diagnostic scan cron job
	 *
	 * @return void
	 */
	private static function schedule_scan_cron() {
		$config    = self::get_scan_config();
		$frequency = $config['frequency'] ?? 'daily';

		// Unschedule existing
		wp_clear_scheduled_hook( 'wpshadow_run_automatic_diagnostic_scan' );

		if ( $frequency === 'manual' ) {
			return;
		}

		// Parse scan time (format: HH:MM)
		$scan_time             = $config['scan_time'] ?? '02:00';
		list( $hour, $minute ) = explode( ':', $scan_time );
		$hour                  = (int) $hour;
		$minute                = (int) $minute;

		// Calculate next run based on frequency
		$now = time();

		if ( $frequency === 'hourly' ) {
			// Hourly: run at XX:MM every hour
			$next_run = $now + ( 60 - (int) ( ( $now - mktime( 0, $minute, 0 ) ) / 60 ) % 60 ) * 60;
			$schedule = 'hourly';
		} elseif ( $frequency === 'daily' ) {
			// Daily: run at HH:MM every day
			$today_time = mktime( $hour, $minute, 0 );
			$next_run   = ( $today_time > $now ) ? $today_time : $today_time + DAY_IN_SECONDS;
			$schedule   = 'daily';
		} else { // weekly
			// Weekly: run at HH:MM every Sunday
			$next_run = self::get_next_weekly_run( $hour, $minute );
			$schedule = 'weekly';
		}

		// Schedule event
		wp_schedule_event( $next_run, $schedule, 'wpshadow_run_automatic_diagnostic_scan' );
	}

	/**
	 * Calculate next weekly run time
	 *
	 * @param int $hour Hour (0-23)
	 * @param int $minute Minute (0-59)
	 * @return int Unix timestamp
	 */
	private static function get_next_weekly_run( $hour, $minute ) {
		$now            = time();
		$today_weekday  = (int) date( 'w' );
		$target_weekday = 0; // Sunday

		// How many days until Sunday?
		$days_until_sunday = ( $target_weekday - $today_weekday + 7 ) % 7;

		if ( $days_until_sunday === 0 ) {
			// Today is Sunday, check if time has passed
			$today_time = mktime( $hour, $minute, 0 );
			if ( $today_time > $now ) {
				return $today_time;
			}
			$days_until_sunday = 7;
		}

		return mktime( $hour, $minute, 0, (int) date( 'm' ), (int) date( 'd' ) + $days_until_sunday );
	}

	/**
	 * Get next scheduled scan time
	 *
	 * @return string Human-readable next scan time, or 'Manual only' if manual
	 */
	public static function get_next_scan_time() {
		$config = self::get_scan_config();

		if ( $config['frequency'] === 'manual' ) {
			return __( 'Manual only', 'wpshadow' );
		}

		$timestamp = wp_next_scheduled( 'wpshadow_run_automatic_diagnostic_scan' );

		if ( ! $timestamp ) {
			return __( 'Not scheduled', 'wpshadow' );
		}

		return sprintf(
			__( '%1$s at %2$s', 'wpshadow' ),
			date_i18n( 'l, F j', $timestamp ),
			date_i18n( 'H:i', $timestamp )
		);
	}

	/**
	 * Run diagnostic scan
	 *
	 * @return array Scan results
	 */
	public static function run_diagnostic_scan() {
		$config  = self::get_scan_config();
		$results = array(
			'timestamp'            => current_time( 'mysql' ),
			'diagnostics_run'      => 0,
			'findings'             => 0,
			'treatments_available' => 0,
		);

		// Run diagnostics
		if ( $config['run_diagnostics'] && class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			// Use the enabled scans method which respects wpshadow_scan_types setting
			$findings                   = \WPShadow\Diagnostics\Diagnostic_Registry::run_enabled_scans();
			$results['diagnostics_run'] = count( $findings );
			$results['findings']        = array_sum( array_column( $findings, 'count' ) );
		}

		// Log scan
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'diagnostic_scan_completed',
				sprintf(
					'Automatic diagnostic scan completed: %d diagnostics, %d findings',
					$results['diagnostics_run'],
					$results['findings']
				),
				'',
				array( 'scan_results' => $results )
			);
		}

		// Store latest scan result
		$scan_history = get_option( 'wpshadow_scan_history', array() );
		array_unshift( $scan_history, $results );
		$scan_history = array_slice( $scan_history, 0, 30 ); // Keep last 30
		update_option( 'wpshadow_scan_history', $scan_history );

		return $results;
	}

	/**
	 * Render scan frequency UI
	 *
	 * @return void
	 */
	public static function render_scan_ui() {
		$config      = self::get_scan_config();
		$frequencies = self::get_available_frequencies();
		$next_scan   = self::get_next_scan_time();
		?>
		<div style="max-width: 800px;">
			<!-- Scan Frequency Selection -->
			<div class="wps-p-24-rounded-8">
				<div class="wps-flex-gap-12-items-center">
					<span class="dashicons dashicons-search" style="font-size: 24px; color: #0073aa;"></span>
					<h3 class="wps-m-0"><?php esc_html_e( 'Scan Frequency Settings', 'wpshadow' ); ?></h3>
				</div>
				<p class="wps-m-0">
					<?php esc_html_e( 'Choose how often WPShadow runs automatic diagnostics to check your site health.', 'wpshadow' ); ?>
				</p>
				
				<form class="wpshadow-scan-frequency-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php wp_nonce_field( 'wpshadow_scan_frequency_nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_update_scan_frequency" />
					
					<!-- Frequency Options -->
					<fieldset style="margin-bottom: 24px;">
						<legend style="font-weight: 500; margin-bottom: 12px; font-size: 14px;">
							<?php esc_html_e( 'Scan Frequency', 'wpshadow' ); ?>
						</legend>
						
						<?php foreach ( $frequencies as $freq_key => $freq_data ) : ?>
							<?php
							$is_selected  = $config['frequency'] === $freq_key;
							$border_color = $is_selected ? '#0073aa' : '#e0e0e0';
							$bg_color     = $is_selected ? '#f0f6fc' : '#fff';
							?>
							<div style="margin-bottom: 12px; padding: 12px; border: 2px solid <?php echo esc_attr( $border_color ); ?>; border-radius: 4px; cursor: pointer; transition: all 0.2s; background: <?php echo esc_attr( $bg_color ); ?>;">
								<label class="wps-flex-gap-12-items-flex-start">
									<input type="radio" name="frequency" value="<?php echo esc_attr( $freq_key ); ?>" <?php checked( $config['frequency'], $freq_key ); ?> style="margin-top: 2px; cursor: pointer; width: 18px; height: 18px;" />
									<div style="flex: 1;">
										<strong class="wps-block"><?php echo esc_html( $freq_data['label'] ); ?></strong>
										<p class="wps-m-4">
											<?php echo esc_html( $freq_data['description'] ); ?>
										</p>
									</div>
								</label>
							</div>
						<?php endforeach; ?>
					</fieldset>
					
					<!-- Scan Time -->
					<div class="wps-p-15-rounded-4" id="scan-time-container" style="display: <?php echo $config['frequency'] === 'manual' ? 'none' : 'block'; ?>;">
						<label class="wps-block">
							<?php esc_html_e( 'Preferred Scan Time:', 'wpshadow' ); ?>
						</label>
						<input type="time" name="scan_time" value="<?php echo esc_attr( $config['scan_time'] ); ?>" class="wps-p-8-rounded-4" />
						<p class="wps-m-6">
							<?php esc_html_e( 'What time should the automatic scan run? (in your server timezone)', 'wpshadow' ); ?>
						</p>
					</div>
					
					<!-- Scan Options -->
					<fieldset class="wps-p-15-rounded-4">
						<legend class="wps-p-0"><?php esc_html_e( 'Scan Behavior', 'wpshadow' ); ?></legend>
						
						<div class="wps-flex-gap-12-items-flex-start">
							<input type="checkbox" name="run_diagnostics" <?php checked( $config['run_diagnostics'] ); ?> id="run-diagnostics" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
							<label for="run-diagnostics" style="cursor: pointer; font-size: 13px;">
								<strong><?php esc_html_e( 'Run Diagnostics', 'wpshadow' ); ?></strong>
								<p class="wps-m-2">
									<?php esc_html_e( 'Always recommended - checks for security, performance, and configuration issues', 'wpshadow' ); ?>
								</p>
							</label>
						</div>
						
						<div class="wps-flex-gap-12-items-flex-start">
							<input type="checkbox" name="run_treatments" <?php checked( $config['run_treatments'] ); ?> id="auto-treatments" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
							<label for="auto-treatments" style="cursor: pointer; font-size: 13px;">
								<strong><?php esc_html_e( 'Auto-Apply Safe Treatments', 'wpshadow' ); ?></strong>
								<p class="wps-m-2">
									<?php esc_html_e( 'Automatically apply low-risk fixes. You can undo any treatment at any time.', 'wpshadow' ); ?>
								</p>
							</label>
						</div>
						
						<div class="wps-flex-gap-12-items-flex-start">
							<input type="checkbox" name="email_results" <?php checked( $config['email_results'] ); ?> id="email-results" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
							<label for="email-results" style="cursor: pointer; font-size: 13px;">
								<strong><?php esc_html_e( 'Email Scan Results', 'wpshadow' ); ?></strong>
								<p class="wps-m-2">
									<?php esc_html_e( 'Send email summary after each automatic scan', 'wpshadow' ); ?>
								</p>
							</label>
						</div>
					</fieldset>
					
					<!-- Update on Plugin/Theme Changes -->
					<fieldset class="wps-p-15-rounded-4">
						<legend class="wps-p-0"><?php esc_html_e( 'Trigger on Updates', 'wpshadow' ); ?></legend>
						
						<div class="wps-flex-gap-12-items-flex-start">
							<input type="checkbox" name="scan_on_plugin_update" <?php checked( $config['scan_on_plugin_update'] ); ?> id="scan-plugin-update" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
							<label for="scan-plugin-update" style="cursor: pointer; font-size: 13px;">
								<strong><?php esc_html_e( 'After Plugin Update', 'wpshadow' ); ?></strong>
								<p class="wps-m-2">
									<?php esc_html_e( 'Automatically scan after any plugin is updated or activated', 'wpshadow' ); ?>
								</p>
							</label>
						</div>
						
						<div class="wps-flex-gap-12-items-flex-start">
							<input type="checkbox" name="scan_on_theme_update" <?php checked( $config['scan_on_theme_update'] ); ?> id="scan-theme-update" style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px;" />
							<label for="scan-theme-update" style="cursor: pointer; font-size: 13px;">
								<strong><?php esc_html_e( 'After Theme Update', 'wpshadow' ); ?></strong>
								<p class="wps-m-2">
									<?php esc_html_e( 'Automatically scan after any theme is updated or activated', 'wpshadow' ); ?>
								</p>
							</label>
						</div>
					</fieldset>
					
					<!-- Save Button -->
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save Scan Settings', 'wpshadow' ); ?>
					</button>
					<span id="wpshadow-scan-status" style="margin-left: 10px;"></span>
				</form>
				
				<!-- Next Scan Info -->
				<div class="wps-p-12-rounded-4">
					<p class="wps-m-0">
						<strong><?php esc_html_e( 'Next Scheduled Scan:', 'wpshadow' ); ?></strong><br />
						<span id="next-scan-time"><?php echo esc_html( $next_scan ); ?></span>
					</p>
				</div>
			</div>

			<!-- Manual Scan Trigger -->
			<div class="wps-p-16-rounded-8">
				<div class="wps-flex-gap-12-items-center">
					<span class="dashicons dashicons-media-play" style="font-size: 24px; color: #2e7d32;"></span>
					<div style="flex: 1;">
						<strong style="color: #2e7d32;"><?php esc_html_e( 'Run Scan Now', 'wpshadow' ); ?></strong>
						<p class="wps-m-4">
							<?php esc_html_e( 'Start a diagnostic scan immediately regardless of schedule.', 'wpshadow' ); ?>
						</p>
					</div>
					<button type="button" id="wpshadow-scan-now-btn" class="button" style="flex-shrink: 0;">
						<?php esc_html_e( 'Start Scan', 'wpshadow' ); ?>
					</button>
				</div>
				<div id="wpshadow-scan-result" style="margin-top: 12px;"></div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Show/hide scan time based on frequency
			$('input[name="frequency"]').on('change', function() {
				var frequency = $(this).val();
				$('#scan-time-container').toggle( frequency !== 'manual' );
			});
			
			// Save scan settings
			$('.wpshadow-scan-frequency-form').on('submit', function(e) {
				e.preventDefault();
				var $form = $(this);
				var $btn = $form.find('button[type="submit"]');
				var $status = $('#wpshadow-scan-status');
				
				var data = {
					action: 'wpshadow_update_scan_frequency',
					nonce: $form.find('input[name="_wpnonce"]').val(),
					frequency: $form.find('input[name="frequency"]:checked').val(),
					scan_time: $form.find('input[name="scan_time"]').val(),
					run_diagnostics: $form.find('input[name="run_diagnostics"]').prop('checked'),
					run_treatments: $form.find('input[name="run_treatments"]').prop('checked'),
					email_results: $form.find('input[name="email_results"]').prop('checked'),
					scan_on_plugin_update: $form.find('input[name="scan_on_plugin_update"]').prop('checked'),
					scan_on_theme_update: $form.find('input[name="scan_on_theme_update"]').prop('checked'),
				};
				
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
				$status.html('');
				
				$.post(ajaxurl, data, function(response) {
					if (response.success) {
						$status.html('<span style="color: #2e7d32;">✓ <?php echo esc_js( __( 'Saved', 'wpshadow' ) ); ?></span>');
						if (response.data.next_scan_time) {
							$('#next-scan-time').text(response.data.next_scan_time);
						}
					} else {
						$status.html('<span style="color: #c62828;">✗ ' + (response.data.message || '<?php echo esc_js( __( 'Error', 'wpshadow' ) ); ?>') + '</span>');
					}
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Scan Settings', 'wpshadow' ) ); ?>');
				});
			});
			
			// Run scan now
			$('#wpshadow-scan-now-btn').on('click', function() {
				var $btn = $(this);
				var $result = $('#wpshadow-scan-result');
				
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Scanning...', 'wpshadow' ) ); ?>');
				$result.html('');
				
				$.post(ajaxurl, {
					action: 'wpshadow_run_scan_now',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_scan_frequency_nonce' ); ?>'
				}, function(response) {
					if (response.success && response.data.results) {
						var results = response.data.results;
						var html = '<div class="wps-p-12-rounded-4">' +
							'<strong>✓ <?php echo esc_js( __( 'Scan complete!', 'wpshadow' ) ); ?></strong><br/>' +
							'Diagnostics run: ' + results.diagnostics_run + '<br/>' +
							'Findings: ' + results.findings +
							'</div>';
						$result.html(html);
					} else {
						$result.html('<div class="wps-p-12-rounded-4">✗ ' + (response.data.message || '<?php echo esc_js( __( 'Error running scan', 'wpshadow' ) ); ?>') + '</div>');
					}
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Start Scan', 'wpshadow' ) ); ?>');
				});
			});
		});
		</script>
		<?php
	}
}
