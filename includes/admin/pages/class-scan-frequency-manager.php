<?php
declare(strict_types=1);

namespace WPShadow\Admin\Pages;

/**
 * Manage WPShadow's automated diagnostic scan schedule.
 *
 * This class is the main scheduling coordinator for the plugin's recurring
 * "Guardian" scans. It stores the admin's preferred frequency, translates
 * that preference into WordPress cron events, runs the scan workflow when the
 * event fires, and renders the settings UI used to control those behaviors.
 *
 * For developers who are new to WordPress, the important concept is that WP
 * cron is not a real system cron daemon. Instead, WordPress checks scheduled
 * events during normal page requests. This class exists so the plugin can hide
 * that complexity behind a predictable API and a straightforward settings page.
 *
 * Philosophy: Helpful Neighbor (#1) - Let users choose their own schedule.
 * Philosophy: Show Value (#9) - Track scan results and improvements.
 *
 * @package WPShadow
 * @since   0.6093.1200
 */
class Scan_Frequency_Manager {

	/**
	 * Option key used to persist scan scheduling preferences.
	 *
	 * The stored value is an associative array containing frequency, preferred
	 * run time, and feature toggles that control whether diagnostics and safe
	 * treatments should run automatically.
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	const OPTION_KEY = 'wpshadow_scan_frequency_settings';

	/**
	 * Return the scan frequency choices shown in the admin UI.
	 *
	 * This method centralizes the labels, descriptions, and icons used by the
	 * settings screen so both rendering and validation can rely on the same
	 * source of truth. Keeping these options in one place prevents drift between
	 * what the UI offers and what the save routine accepts.
	 *
	 * @since  0.6093.1200
	 * @return array<string,array<string,string>> Registered frequency options.
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
	 * Read the stored scan configuration and merge it with defaults.
	 *
	 * WordPress options can be missing, partially populated, or overwritten with
	 * unexpected data by imports or older plugin versions. This helper normalizes
	 * the saved value into a complete configuration array and quietly writes back
	 * defaults when required so the rest of the class can rely on predictable
	 * keys being present.
	 *
	 * @since  0.6093.1200
	 * @return array<string,mixed> Normalized scan configuration.
	 */
	public static function get_scan_config() {
		$defaults = array(
			'frequency'             => 'daily',
			'scan_time'             => '02:00', // 2 AM
			'run_diagnostics'       => true,
			'run_treatments'        => true,
			'scan_on_plugin_update' => true,
			'scan_on_theme_update'  => true,
		);

		$saved  = get_option( self::OPTION_KEY, array() );
		$config = wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );

		if ( ! is_array( $saved ) || array_diff_key( $defaults, $saved ) ) {
			update_option( self::OPTION_KEY, $config );
		}

		return $config;
	}

	/**
	 * Update a single scan configuration value and reschedule cron if needed.
	 *
	 * This method is intentionally narrow: it updates one setting key at a time,
	 * validates values that affect scheduling, persists the full option payload,
	 * and then refreshes WP-Cron when the change impacts when scans should run.
	 * It also logs the change so admins can later understand when scheduling
	 * behavior was modified.
	 *
	 * @since  0.6093.1200
	 * @param  string $key   Configuration key to update.
	 * @param  mixed  $value New value for the specified key.
	 * @return bool True when the option was updated successfully.
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
	 * Register the next automatic scan event with WP-Cron.
	 *
	 * WordPress stores scheduled events as timestamps plus recurrence names.
	 * This method converts the human-friendly configuration values into the next
	 * valid timestamp for the selected cadence, clears any previously scheduled
	 * event to avoid duplicates, and then registers the replacement event.
	 *
	 * @since  0.6093.1200
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
	 * Calculate the next Sunday run time for weekly scans.
	 *
	 * Weekly schedules are slightly more complex than hourly or daily schedules
	 * because the code must account for both the target weekday and whether that
	 * time has already passed today. The method always returns a future Unix
	 * timestamp so it can be passed directly to wp_schedule_event().
	 *
	 * @since  0.6093.1200
	 * @param  int $hour   Hour portion of the preferred time, in 24-hour format.
	 * @param  int $minute Minute portion of the preferred time.
	 * @return int Unix timestamp for the next eligible Sunday run.
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
	 * Return the next scheduled scan as a localized human-readable string.
	 *
	 * This is used only for display. It converts the raw timestamp returned by
	 * wp_next_scheduled() into a label that non-technical admins can understand,
	 * while also handling manual mode and unscheduled states explicitly.
	 *
	 * @since  0.6093.1200
	 * @return string Human-readable next scan time or a status label.
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
	 * Run a full WPShadow Guardian scan and optionally apply safe treatments.
	 *
	 * This is the operational heart of automated scanning. It raises execution
	 * limits when possible, runs enabled diagnostics, persists the resulting site
	 * state, optionally applies treatments that the plugin considers safe or has
	 * been pre-approved by the admin, and then stores a short scan history entry
	 * for dashboard and reporting consumers.
	 *
	 * For WordPress newcomers: diagnostics are read-only checks, while treatments
	 * are the fix routines that can change settings or files. This method can do
	 * both, but only in a controlled order so the plugin can re-scan after fixes
	 * and show the updated result.
	 *
	 * @since  0.6093.1200
	 * @param  bool $force_diagnostics Whether to bypass saved scan toggles and force diagnostic execution.
	 * @return array<string,int|string> Summary metrics for the completed run.
	 */
	public static function run_diagnostic_scan( bool $force_diagnostics = false ) {
		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}

		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 120 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Manual Guardian runs can exceed default local execution limits.
		}

		$config  = self::get_scan_config();
		$completed_at = time();
		$results = array(
			'timestamp'            => current_time( 'mysql' ),
			'diagnostics_run'      => 0,
			'findings'             => 0,
			'treatments_available' => 0,
			'treatments_applied'   => 0,
			'treatments_verified'  => 0,
			'treatments_passed'    => 0,
			'treatments_failed'    => 0,
		);
		$findings = array();

		$should_run_diagnostics = $force_diagnostics || ! empty( $config['run_diagnostics'] );

		// Run diagnostics
		if ( $should_run_diagnostics && class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			// Use the enabled scans method which respects wpshadow_scan_types setting
			$findings = \WPShadow\Diagnostics\Diagnostic_Registry::run_enabled_scans( $force_diagnostics );
			$results['diagnostics_run'] = count( $findings );
			$results['findings']        = array_sum( array_column( $findings, 'count' ) );

			self::persist_scan_state( $findings, $completed_at );

			if ( ! empty( $config['run_treatments'] ) ) {
				$treatment_results = self::apply_automatic_treatments( $findings );
				$results['treatments_available'] = (int) $treatment_results['available'];
				$results['treatments_applied']   = (int) $treatment_results['applied'];
				$results['treatments_verified']  = (int) ( $treatment_results['verified'] ?? 0 );
				$results['treatments_passed']    = (int) ( $treatment_results['verified_passed'] ?? 0 );
				$results['treatments_failed']    = (int) ( $treatment_results['verified_failed'] ?? 0 );

				// Refresh findings/state after automated fixes so report cards reflect post-treatment status.
				if ( $results['treatments_applied'] > 0 ) {
					$completed_at = time();
					$findings = \WPShadow\Diagnostics\Diagnostic_Registry::run_enabled_scans( $force_diagnostics );
					$results['diagnostics_run'] = count( $findings );
					$results['findings']        = array_sum( array_column( $findings, 'count' ) );
					self::persist_scan_state( $findings, $completed_at );
				}
			}
		}

		do_action( 'wpshadow_diagnostics_completed' );
		if ( class_exists( '\WPShadow\Core\Dashboard_Cache' ) && method_exists( '\WPShadow\Core\Dashboard_Cache', 'invalidate_cache' ) ) {
			\WPShadow\Core\Dashboard_Cache::invalidate_cache();
		}

		// Log scan
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'diagnostic_scan_completed',
				sprintf(
					'Guardian run completed: %d diagnostics, %d findings, %d treatments applied',
					$results['diagnostics_run'],
					$results['findings'],
					$results['treatments_applied']
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
	 * Persist findings and run-state timestamps for dashboard/reporting consumers.
	 *
	 * @param array<int,array<string,mixed>> $findings Findings from registry execution.
	 * @param int                             $completed_at Unix timestamp for run completion.
	 * @return void
	 */
	private static function persist_scan_state( array $findings, int $completed_at ): void {
		$indexed = array();
		foreach ( $findings as $finding ) {
			if ( ! is_array( $finding ) ) {
				continue;
			}

			$finding_id = isset( $finding['id'] ) ? sanitize_key( (string) $finding['id'] ) : '';
			if ( '' !== $finding_id ) {
				$indexed[ $finding_id ] = $finding;
			} else {
				$indexed[] = $finding;
			}
		}

		update_option( 'wpshadow_site_findings', $indexed );
		update_option( 'wpshadow_last_quick_checks', $completed_at );
		update_option( 'wpshadow_last_heavy_tests', $completed_at );

		$stats = class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' )
			? \WPShadow\Diagnostics\Diagnostic_Registry::get_last_run_stats()
			: array();

		if ( function_exists( 'wpshadow_record_diagnostic_run_coverage' ) && is_array( $stats ) ) {
			$executed = isset( $stats['executed'] ) && is_array( $stats['executed'] ) ? $stats['executed'] : array();
			\wpshadow_record_diagnostic_run_coverage( $executed, $completed_at );
		}

		if ( function_exists( 'wpshadow_record_diagnostic_test_states' ) && is_array( $stats ) ) {
			$results = isset( $stats['results'] ) && is_array( $stats['results'] ) ? $stats['results'] : array();
			$state_payload = array();

			foreach ( $results as $class_name => $result ) {
				if ( ! is_string( $class_name ) || ! is_array( $result ) ) {
					continue;
				}

				$status = isset( $result['status'] ) ? (string) $result['status'] : '';
				if ( 'passed' !== $status && 'failed' !== $status ) {
					continue;
				}

				$state_payload[ $class_name ] = array(
					'status'     => $status,
					'category'   => isset( $result['category'] ) ? (string) $result['category'] : '',
					'finding_id' => isset( $result['finding_id'] ) ? (string) $result['finding_id'] : '',
				);
			}

			if ( ! empty( $state_payload ) ) {
				\wpshadow_record_diagnostic_test_states( $state_payload, $completed_at );
			}
		}
	}

	/**
	 * Auto-apply safe treatments for the current finding set.
	 *
	 * The plugin separates discovery from remediation. This helper takes the
	 * findings produced by diagnostics, looks up matching treatment classes, and
	 * applies only the low-risk treatments or the moderate-risk ones the admin has
	 * explicitly pre-approved. It also performs one verification pass per finding
	 * so the scan summary can report whether fixes actually held.
	 *
	 * @since  0.6093.1200
	 * @param array<int,array<string,mixed>> $findings Current findings list.
	 * @return array{available:int,applied:int,verified:int,verified_passed:int,verified_failed:int}
	 */
	private static function apply_automatic_treatments( array $findings ): array {
		if ( ! class_exists( '\WPShadow\Treatments\Treatment_Registry' ) ) {
			return array(
				'available' => 0,
				'applied'   => 0,
				'verified'  => 0,
				'verified_passed' => 0,
				'verified_failed' => 0,
			);
		}

		$always_apply = get_option( 'wpshadow_auto_apply_treatments', array() );
		$always_apply = is_array( $always_apply ) ? array_map( 'sanitize_key', $always_apply ) : array();

		$available = 0;
		$applied   = 0;
		$verified  = 0;
		$verified_passed = 0;
		$verified_failed = 0;
		$verified_once = array();

		foreach ( $findings as $finding ) {
			if ( ! is_array( $finding ) ) {
				continue;
			}

			$finding_id = isset( $finding['id'] ) ? sanitize_key( (string) $finding['id'] ) : '';
			if ( '' === $finding_id ) {
				continue;
			}

			try {
				$treatment_class = \WPShadow\Treatments\Treatment_Registry::get_treatment( $finding_id );
			} catch ( \Throwable $exception ) {
				\WPShadow\Core\Error_Handler::log_error(
					sprintf( 'WPShadow Guardian treatment lookup failed for %s', $finding_id ),
					$exception
				);
				continue;
			}
			if ( ! is_string( $treatment_class ) || '' === $treatment_class || ! class_exists( $treatment_class ) ) {
				continue;
			}

			if ( method_exists( $treatment_class, 'can_apply' ) && ! $treatment_class::can_apply() ) {
				continue;
			}

			$risk_level = method_exists( $treatment_class, 'get_risk_level' )
				? (string) $treatment_class::get_risk_level()
				: 'moderate';

			$available++;
			$should_apply = ( 'safe' === $risk_level ) || ( 'moderate' === $risk_level && in_array( $finding_id, $always_apply, true ) );
			if ( ! $should_apply ) {
				continue;
			}

			try {
				$result = \WPShadow\Treatments\Treatment_Registry::apply_treatment( $finding_id, false );
			} catch ( \Throwable $exception ) {
				\WPShadow\Core\Error_Handler::log_error(
					sprintf( 'WPShadow Guardian treatment apply failed for %s', $finding_id ),
					$exception
				);
				continue;
			}
			if ( is_array( $result ) && ! empty( $result['success'] ) ) {
				$applied++;

				// Verify each finding at most once per Guardian run to avoid any recheck loops.
				if ( ! isset( $verified_once[ $finding_id ] ) ) {
					$verified_once[ $finding_id ] = true;
					$verification = self::verify_finding_after_treatment( $finding_id );
					if ( ! empty( $verification['verified'] ) ) {
						$verified++;
						if ( 'passed' === ( $verification['status'] ?? '' ) ) {
							$verified_passed++;
						} else {
							$verified_failed++;
						}
					}
				}
			}
		}

		return array(
			'available' => $available,
			'applied'   => $applied,
			'verified'  => $verified,
			'verified_passed' => $verified_passed,
			'verified_failed' => $verified_failed,
		);
	}

	/**
	 * Re-run the diagnostic for a finding immediately after auto-treatment apply.
	 *
	 * This is a single verification pass and does not trigger any additional
	 * treatment application, preventing recursive fix/recheck loops.
	 *
	 * @param string $finding_id Finding/diagnostic run key.
	 * @return array{verified:bool,status:string}
	 */
	private static function verify_finding_after_treatment( string $finding_id ): array {
		if ( '' === $finding_id || ! class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			return array(
				'verified' => false,
				'status'   => '',
			);
		}

		$diagnostic_class = '';
		$definitions = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_definitions();
		foreach ( $definitions as $definition ) {
			if ( ! is_array( $definition ) ) {
				continue;
			}

			$run_key = isset( $definition['run_key'] ) ? sanitize_key( (string) $definition['run_key'] ) : '';
			if ( $run_key !== $finding_id ) {
				continue;
			}

			$diagnostic_class = isset( $definition['class'] ) ? (string) $definition['class'] : '';
			break;
		}

		if ( '' === $diagnostic_class || ! class_exists( $diagnostic_class ) ) {
			return array(
				'verified' => false,
				'status'   => '',
			);
		}

		try {
			$verification_result = null;
			if ( method_exists( $diagnostic_class, 'execute' ) ) {
				// force=true guarantees a real post-treatment recheck rather than schedule/cache skip.
				$verification_result = call_user_func( array( $diagnostic_class, 'execute' ), true );
			} elseif ( method_exists( $diagnostic_class, 'check' ) ) {
				$verification_result = call_user_func( array( $diagnostic_class, 'check' ) );
			} else {
				return array(
					'verified' => false,
					'status'   => '',
				);
			}

			return array(
				'verified' => true,
				'status'   => ( is_array( $verification_result ) && ! empty( $verification_result ) ) ? 'failed' : 'passed',
			);
		} catch ( \Throwable $exception ) {
			\WPShadow\Core\Error_Handler::log_error(
				sprintf( 'WPShadow Guardian verification failed for %s', $finding_id ),
				$exception
			);
			return array(
				'verified' => false,
				'status'   => '',
			);
		}
	}

	/**
	 * Render the scan settings panel shown in the admin interface.
	 *
	 * The markup is kept in PHP rather than a separate template because the UI is
	 * tightly coupled to the normalized configuration returned by this class and
	 * is only used in one place. The method reads current settings, builds the
	 * form, and prints the controls that let an admin understand and change how
	 * scheduled scanning behaves.
	 *
	 * @since  0.6093.1200
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
