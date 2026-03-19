<?php
declare(strict_types=1);

namespace WPShadow\Settings;

/**
 * Report Scheduler Manager
 *
 * Manages automatic scheduled email delivery of reports.
 * Philosophy: Free as Possible (#2) - Scheduling available to all users
 * Philosophy: Show Value (#9) - Automated insights delivered on schedule
 *
 * @since 1.6093.1200
 * @package WPShadow
 */
class Report_Scheduler {

	/**
	 * Option key for scheduled reports
	 */
	const OPTION_KEY = 'wpshadow_scheduled_reports';

	/**
	 * Available schedule frequencies
	 *
	 * @return array Frequency options
	 */
	public static function get_frequencies() {
		return array(
			'daily'     => __( 'Daily (every morning at 8 AM)', 'wpshadow' ),
			'weekly'    => __( 'Weekly (every Monday at 9 AM)', 'wpshadow' ),
			'biweekly'  => __( 'Bi-weekly (1st and 15th at 9 AM)', 'wpshadow' ),
			'monthly'   => __( 'Monthly (1st of month at 9 AM)', 'wpshadow' ),
			'quarterly' => __( 'Quarterly (every 3 months)', 'wpshadow' ),
			'disabled'  => __( 'Disabled', 'wpshadow' ),
		);
	}

	/**
	 * Get all scheduled reports
	 *
	 * @return array Scheduled reports configuration
	 */
	public static function get_all_schedules() {
		return get_option(
			self::OPTION_KEY,
			array(
				'executive_report' => array(
					'enabled'                 => false,
					'frequency'               => 'weekly',
					'recipients'              => array(),
					'template'                => 'report_executive',
					'include_recommendations' => true,
				),
				'detailed_report'  => array(
					'enabled'                 => false,
					'frequency'               => 'monthly',
					'recipients'              => array(),
					'template'                => 'report_detailed',
					'include_recommendations' => true,
				),
			)
		);
	}

	/**
	 * Update report schedule
	 *
	 * @param string $report_type Report type identifier
	 * @param array  $config Report schedule configuration
	 * @return bool Success status
	 */
	public static function update_schedule( $report_type, $config ) {
		if ( empty( $report_type ) ) {
			return false;
		}

		// Validate configuration
		$validated = self::validate_schedule_config( $config );
		if ( ! $validated ) {
			return false;
		}

		// Get existing schedules
		$schedules = self::get_all_schedules();

		// Update schedule
		$schedules[ $report_type ] = $config;

		// Save schedules
		$result = update_option( self::OPTION_KEY, $schedules );

		// Schedule/unschedule cron job
		if ( $config['enabled'] ) {
			self::schedule_report_cron( $report_type, $config['frequency'] );
		} else {
			self::unschedule_report_cron( $report_type );
		}

		// Log activity
		if ( $result && class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'report_schedule_updated',
				sprintf( 'Report schedule updated: %s (frequency: %s)', $report_type, $config['frequency'] ),
				'',
				array(
					'report_type' => $report_type,
					'frequency'   => $config['frequency'],
				)
			);
		}

		return $result;
	}

	/**
	 * Validate schedule configuration
	 *
	 * @param array $config Configuration to validate
	 * @return bool True if valid
	 */
	private static function validate_schedule_config( $config ) {
		// Check required fields
		if ( ! isset( $config['enabled'], $config['frequency'], $config['recipients'] ) ) {
			return false;
		}

		// Validate frequency
		$valid_frequencies = array_keys( self::get_frequencies() );
		if ( ! in_array( $config['frequency'], $valid_frequencies, true ) ) {
			return false;
		}

		// Validate recipients (array of valid emails).
		if ( ! is_array( $config['recipients'] ) ) {
			return false;
		}

		foreach ( $config['recipients'] as $recipient ) {
			if ( ! is_email( $recipient ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Schedule cron job for report delivery
	 *
	 * @param string $report_type Report type
	 * @param string $frequency Frequency string
	 * @return void
	 */
	private static function schedule_report_cron( $report_type, $frequency ) {
		$hook = 'wpshadow_deliver_scheduled_report_' . $report_type;

		// Unschedule any existing
		wp_clear_scheduled_hook( $hook );

		// Calculate next run
		$next_run = self::calculate_next_run( $frequency );

		// Schedule new
		wp_schedule_event( $next_run, $frequency, $hook );
	}

	/**
	 * Unschedule cron job for report delivery
	 *
	 * @param string $report_type Report type
	 * @return void
	 */
	private static function unschedule_report_cron( $report_type ) {
		$hook = 'wpshadow_deliver_scheduled_report_' . $report_type;
		wp_clear_scheduled_hook( $hook );
	}

	/**
	 * Calculate next run time for given frequency
	 *
	 * @param string $frequency Frequency string
	 * @return int Unix timestamp
	 */
	private static function calculate_next_run( $frequency ) {
		$now = time();

		switch ( $frequency ) {
			case 'daily':
				// Tomorrow at 8 AM
				$time = strtotime( 'tomorrow 08:00', $now );
				break;

			case 'weekly':
				// Next Monday at 9 AM
				$time = strtotime( 'next Monday 09:00', $now );
				break;

			case 'biweekly':
				// In 2 weeks at 9 AM
				$time = strtotime( '+2 weeks 09:00', $now );
				break;

			case 'monthly':
				// Next month 1st at 9 AM
				$time = strtotime( 'first day of next month 09:00', $now );
				break;

			case 'quarterly':
				// In 3 months at 9 AM
				$time = strtotime( '+3 months 09:00', $now );
				break;

			default:
				$time = strtotime( 'tomorrow 08:00', $now );
		}

		return $time;
	}

	/**
	 * Send scheduled report via email
	 *
	 * @param string $report_type Report type
	 * @return bool Success status
	 */
	public static function send_scheduled_report( $report_type ) {
		$schedules = self::get_all_schedules();

		if ( ! isset( $schedules[ $report_type ] ) || ! $schedules[ $report_type ]['enabled'] ) {
			return false;
		}

		$config = $schedules[ $report_type ];

		// Get report using existing Report_Engine
		if ( ! class_exists( '\WPShadow\Reports\Report_Engine' ) ) {
			return false;
		}

		$report = \WPShadow\Reports\Report_Engine::generate_report(
			date( 'Y-m-d', strtotime( '-1 day' ) ),
			date( 'Y-m-d' ),
			$config['template'],
			true
		);

		if ( empty( $report ) ) {
			return false;
		}

		// Render report to HTML
		if ( ! class_exists( '\WPShadow\Reports\Report_Renderer' ) ) {
			return false;
		}

		$html_content = \WPShadow\Reports\Report_Renderer::render_html( $report );

		// Get email template
		$template_html = Email_Template_Manager::get_template( $config['template'], 'html' );

		// Prepare email content
		$email_content = str_replace(
			array( '{title}', '{content}', '{footer}' ),
			array(
				sprintf( __( '%s Report', 'wpshadow' ), ucfirst( str_replace( '_', ' ', $report_type ) ) ),
				$html_content,
				get_bloginfo( 'name' ),
			),
			$template_html
		);

		// Send to all recipients
		$success = true;
		foreach ( $config['recipients'] as $recipient ) {
			$result = wp_mail(
				$recipient,
				sprintf( __( '[%1$s] %2$s Report', 'wpshadow' ), get_bloginfo( 'name' ), ucfirst( str_replace( '_', ' ', $report_type ) ) ),
				$email_content,
				array( 'Content-Type: text/html; charset=UTF-8' )
			);

			if ( ! $result ) {
				$success = false;
			}
		}

		// Log activity
		if ( $success && class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'scheduled_report_sent',
				sprintf( 'Scheduled report sent: %s to %d recipient(s)', $report_type, count( $config['recipients'] ) ),
				'',
				array(
					'report_type' => $report_type,
					'recipients'  => $config['recipients'],
				)
			);
		}

		return $success;
	}

	/**
	 * Render schedule configuration UI
	 *
	 * @return void
	 */
	public static function render_scheduler_ui() {
		$version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0';

		wp_enqueue_script(
			'wpshadow-report-scheduler',
			WPSHADOW_URL . 'assets/js/report-scheduler.js',
			array( 'jquery' ),
			$version,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-report-scheduler',
			'wpsReportScheduler',
			'wpshadow_schedule_report_nonce',
			array(
				'savingText'   => __( 'Saving...', 'wpshadow' ),
				'savedText'    => __( 'Saved', 'wpshadow' ),
				'errorText'    => __( 'Error', 'wpshadow' ),
				'saveButton'   => __( 'Save Schedule', 'wpshadow' ),
			)
		);

		$schedules   = self::get_all_schedules();
		$frequencies = self::get_frequencies();
		?>
		<div class="wps-report-scheduler-container">
			<!-- Executive Report Schedule -->
			<div class="wps-p-24-rounded-8">
				<div class="wps-flex-gap-12-items-center">
					<span class="dashicons dashicons-chart-area" class="wps-report-scheduler-icon"></span>
					<h3 class="wps-m-0"><?php esc_html_e( 'Executive Report Schedule', 'wpshadow' ); ?></h3>
				</div>
				<p class="wps-m-0"><?php esc_html_e( 'Receive a high-level summary of your site\'s health and recommendations.', 'wpshadow' ); ?></p>
				
				<form class="wpshadow-schedule-form" data-report-type="executive_report">
					<?php wp_nonce_field( 'wpshadow_schedule_report_nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_update_report_schedule" />
					<input type="hidden" name="report_type" value="executive_report" />
					
					<!-- Enable toggle -->
					<div class="wps-flex-gap-12-items-center">
						<?php
						$exec_config = $schedules['executive_report'] ?? array();
						$is_enabled  = isset( $exec_config['enabled'] ) && $exec_config['enabled'];
						?>
						<input type="checkbox" name="enabled" <?php checked( $is_enabled ); ?> id="exec-report-enabled" class="wps-report-scheduler-checkbox" />
						<label for="exec-report-enabled" class="wps-report-scheduler-label">
							<?php esc_html_e( 'Enable automatic delivery', 'wpshadow' ); ?>
						</label>
					</div>
					
					<!-- Frequency -->
					<div class="wps-report-section-margin">
						<label class="wps-block">
							<?php esc_html_e( 'Frequency:', 'wpshadow' ); ?>
						</label>
						<div class="wps-select-wrapper">
							<select name="frequency" class="wps-select">
								<?php
								$freq = $exec_config['frequency'] ?? 'weekly';
								foreach ( $frequencies as $value => $label ) :
									if ( $value === 'disabled' ) {
										continue;
									}
									?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $freq, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					
					<!-- Recipients -->
					<div class="wps-report-section-margin">
						<label class="wps-block">
							<?php esc_html_e( 'Send to:', 'wpshadow' ); ?>
						</label>
						<input type="email" name="recipients" placeholder="email@example.com" class="wps-input" value="<?php echo esc_attr( implode( ', ', $exec_config['recipients'] ?? array() ) ); ?>" />
						<p class="wps-m-6">
							<?php esc_html_e( 'Separate multiple emails with commas', 'wpshadow' ); ?>
						</p>
					</div>
					
					<!-- Save Button -->
					<button type="submit" class="wps-btn wps-btn-primary">
						<?php esc_html_e( 'Save Schedule', 'wpshadow' ); ?>
					</button>
					<span class="schedule-status" class="wps-report-status"></span>
				</form>
			</div>

			<!-- Detailed Report Schedule -->
			<div class="wps-p-24-rounded-8">
				<div class="wps-flex-gap-12-items-center">
					<span class="dashicons dashicons-chart-line" class="wps-report-scheduler-icon"></span>
					<h3 class="wps-m-0"><?php esc_html_e( 'Detailed Report Schedule', 'wpshadow' ); ?></h3>
				</div>
				<p class="wps-m-0"><?php esc_html_e( 'Receive comprehensive technical details about all findings and recommendations.', 'wpshadow' ); ?></p>
				
				<form class="wpshadow-schedule-form" data-report-type="detailed_report">
					<?php wp_nonce_field( 'wpshadow_schedule_report_nonce' ); ?>
					<input type="hidden" name="action" value="wpshadow_update_report_schedule" />
					<input type="hidden" name="report_type" value="detailed_report" />
					
					<!-- Enable toggle -->
					<div class="wps-flex-gap-12-items-center">
						<?php
						$det_config = $schedules['detailed_report'] ?? array();
						$is_enabled = isset( $det_config['enabled'] ) && $det_config['enabled'];
						?>
						<input type="checkbox" name="enabled" <?php checked( $is_enabled ); ?> id="det-report-enabled" class="wps-report-scheduler-checkbox" />
						<label for="det-report-enabled" class="wps-report-scheduler-label">
							<?php esc_html_e( 'Enable automatic delivery', 'wpshadow' ); ?>
						</label>
					</div>
					
					<!-- Frequency -->
					<div class="wps-report-section-margin">
						<label class="wps-block">
							<?php esc_html_e( 'Frequency:', 'wpshadow' ); ?>
						</label>
						<div class="wps-select-wrapper">
							<select name="frequency" class="wps-select">
								<?php
								$freq = $det_config['frequency'] ?? 'monthly';
								foreach ( $frequencies as $value => $label ) :
									if ( $value === 'disabled' ) {
										continue;
									}
									?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $freq, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					
					<!-- Recipients -->
					<div class="wps-report-section-margin">
						<label class="wps-block">
							<?php esc_html_e( 'Send to:', 'wpshadow' ); ?>
						</label>
						<input type="email" name="recipients" placeholder="email@example.com" class="wps-input" value="<?php echo esc_attr( implode( ', ', $det_config['recipients'] ?? array() ) ); ?>" />
						<p class="wps-m-6">
							<?php esc_html_e( 'Separate multiple emails with commas', 'wpshadow' ); ?>
						</p>
					</div>
					
					<!-- Save Button -->
					<button type="submit" class="wps-btn wps-btn-primary">
						<?php esc_html_e( 'Save Schedule', 'wpshadow' ); ?>
					</button>
					<span class="schedule-status" class="wps-report-status"></span>
				</form>
			</div>
		</div>

		<?php
	}
}
