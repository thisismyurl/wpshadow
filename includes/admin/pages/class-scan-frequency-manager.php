<?php
declare(strict_types=1);

namespace WPShadow\Admin\Pages;

/**
 * Scan Frequency Manager
 *
 * Manages diagnostic scan scheduling and frequency configuration.
 * Philosophy: Helpful Neighbor (#1) - Let users choose their own schedule
 * Philosophy: Show Value (#9) - Track scan results and improvements
 *
 * @since 0.6093.1200
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
		$version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0';



		$config      = self::get_scan_config();
		$frequencies = self::get_available_frequencies();
		$next_scan   = self::get_next_scan_time();
		?>
		<div class="wps-scan-container">
			<!-- Scan Frequency Selection -->
			<div class="wps-p-24-rounded-8">
				<div class="wps-flex-gap-12-items-center">
					<span class="dashicons dashicons-search" class="wps-scan-icon"></span>
					<h3 class="wps-m-0"><?php esc_html_e( 'Scan Frequency Settings', 'wpshadow' ); ?></h3>
				</div>
				<p class="wps-m-0">
					<?php esc_html_e( 'Choose how often WPShadow runs automatic diagnostics to check your site health.', 'wpshadow' ); ?>
				</p>

				<form class="wpshadow-scan-frequency-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php wp_nonce_field( 'wpshadow_scan_frequency_nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_update_scan_frequency" />

					<!-- Frequency Options -->
					<fieldset class="wps-scan-fieldset">
						<legend class="wps-scan-legend">
							<?php esc_html_e( 'Scan Frequency', 'wpshadow' ); ?>
						</legend>

						<?php foreach ( $frequencies as $freq_key => $freq_data ) : ?>
							<?php
							$is_selected  = $config['frequency'] === $freq_key;
							?>
							<div class="wps-scan-frequency-option <?php echo $is_selected ? 'wps-scan-frequency-option-selected' : 'wps-scan-frequency-option-default'; ?>">
								<label class="wps-flex-gap-12-items-flex-start">
									<input type="radio" name="frequency" value="<?php echo esc_attr( $freq_key ); ?>" <?php checked( $config['frequency'], $freq_key ); ?> class="wps-scan-input-radio" />
									<div class="wps-scan-flex-expand">
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
					<div class="wps-p-15-rounded-4 <?php echo $config['frequency'] === 'manual' ? 'wps-scan-time-hidden' : ''; ?>" id="scan-time-container">
						<label class="wps-block">
							<?php esc_html_e( 'Preferred Scan Time:', 'wpshadow' ); ?>
						</label>
						<input type="time" name="scan_time" value="<?php echo esc_attr( $config['scan_time'] ); ?>" class="wps-p-8-rounded-4" />
						<p class="wps-m-6">
									<?php esc_html_e( 'What time should the automatic scan run?', 'wpshadow' ); ?>
						</p>
					</div>

					<!-- Scan Options -->
					<fieldset class="wps-p-15-rounded-4">
						<legend class="wps-p-0"><?php esc_html_e( 'Scan Behavior', 'wpshadow' ); ?></legend>

						<div class="wps-flex-gap-12-items-flex-start">
							<label class="wps-toggle" for="run-diagnostics">
								<input type="checkbox" name="run_diagnostics" <?php checked( $config['run_diagnostics'] ); ?> id="run-diagnostics" value="1" />
								<span class="wps-toggle-slider"></span>
								<strong><?php esc_html_e( 'Run Diagnostics', 'wpshadow' ); ?></strong>
							</label>
							<div class="wps-scan-label">
								<p class="wps-m-2">
									<?php esc_html_e( 'Always recommended - checks for security, performance, and configuration issues', 'wpshadow' ); ?>
								</p>
							</div>
						</div>

						<div class="wps-flex-gap-12-items-flex-start">
							<label class="wps-toggle" for="auto-treatments">
								<input type="checkbox" name="run_treatments" <?php checked( $config['run_treatments'] ); ?> id="auto-treatments" value="1" />
								<span class="wps-toggle-slider"></span>
								<strong><?php esc_html_e( 'Auto-Apply Safe Treatments', 'wpshadow' ); ?></strong>
							</label>
							<div class="wps-scan-label">
								<p class="wps-m-2">
									<?php esc_html_e( 'Automatically apply low-risk fixes. You can undo any treatment at any time.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>

					</fieldset>

					<!-- Update on Plugin/Theme Changes -->
					<fieldset class="wps-p-15-rounded-4">
						<legend class="wps-p-0"><?php esc_html_e( 'Trigger on Updates', 'wpshadow' ); ?></legend>

						<div class="wps-flex-gap-12-items-flex-start">
							<label class="wps-toggle" for="scan-plugin-update">
								<input type="checkbox" name="scan_on_plugin_update" <?php checked( $config['scan_on_plugin_update'] ); ?> id="scan-plugin-update" value="1" />
								<span class="wps-toggle-slider"></span>
								<strong><?php esc_html_e( 'After Plugin Update', 'wpshadow' ); ?></strong>
							</label>
							<div class="wps-scan-label">
								<p class="wps-m-2">
									<?php esc_html_e( 'Automatically scan after any plugin is updated or activated', 'wpshadow' ); ?>
								</p>
							</div>
						</div>

						<div class="wps-flex-gap-12-items-flex-start">
							<label class="wps-toggle" for="scan-theme-update">
								<input type="checkbox" name="scan_on_theme_update" <?php checked( $config['scan_on_theme_update'] ); ?> id="scan-theme-update" value="1" />
								<span class="wps-toggle-slider"></span>
								<strong><?php esc_html_e( 'After Theme Update', 'wpshadow' ); ?></strong>
							</label>
							<div class="wps-scan-label">
								<p class="wps-m-2">
									<?php esc_html_e( 'Automatically scan after any theme is updated or activated', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</fieldset>

					<!-- Save Button -->
					<button type="submit" class="wps-btn wps-btn-primary">
						<?php esc_html_e( 'Save Scan Settings', 'wpshadow' ); ?>
					</button>
					<span id="wpshadow-scan-status" class="wps-scan-status"></span>
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
					<span class="dashicons dashicons-media-play" class="wps-scan-success-icon"></span>
					<div class="wps-scan-flex-expand">
						<strong class="wps-scan-run-now-title"><?php esc_html_e( 'Run Scan Now', 'wpshadow' ); ?></strong>
						<p class="wps-m-4">
							<?php esc_html_e( 'Start a diagnostic scan immediately regardless of schedule.', 'wpshadow' ); ?>
						</p>
					</div>
					<button type="button" id="wpshadow-scan-now-btn" class="button wps-scan-now-btn">
						<?php esc_html_e( 'Start Scan', 'wpshadow' ); ?>
					</button>
				</div>
				<div id="wpshadow-scan-result" class="wps-scan-result"></div>
			</div>
		</div>
		<?php
	}
}
