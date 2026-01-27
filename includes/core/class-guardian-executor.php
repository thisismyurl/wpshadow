<?php
/**
 * Guardian Executor
 *
 * Central execution engine for WPShadow Guardian system.
 * Intelligently executes diagnostics based on performance impact,
 * scheduling rules, and server load conditions.
 *
 * Philosophy: Shows value (#9) through intelligent, automatic health monitoring
 * that works quietly in the background without impacting user experience.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Core;

use WPShadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guardian_Executor Class
 *
 * Executes diagnostics automatically based on:
 * - Performance impact classification
 * - Scheduling rules (background vs off-hours)
 * - Server load conditions
 * - User configuration
 *
 * @since 1.2601.2148
 */
class Guardian_Executor {

	/**
	 * Maximum execution time per heartbeat cycle (milliseconds)
	 *
	 * @var int
	 */
	const MAX_HEARTBEAT_MS = 100;

	/**
	 * Maximum diagnostics to run per heartbeat cycle
	 *
	 * @var int
	 */
	const MAX_DIAGNOSTICS_PER_HEARTBEAT = 3;

	/**
	 * Maximum server load threshold (percentage)
	 *
	 * @var int
	 */
	const MAX_SERVER_LOAD_PERCENT = 70;

	/**
	 * Off-peak hours (24-hour format)
	 *
	 * @var array
	 */
	const DEFAULT_OFF_PEAK_HOURS = array( 2, 3, 4, 5, 6 );

	/**
	 * Initialize Guardian Executor
	 *
	 * @since 1.2601.2148
	 * @return void
	 */
	public static function init(): void {
		// Hook into scheduled events
		add_action( 'wpshadow_guardian_deep_scan', array( __CLASS__, 'execute_scheduled_diagnostics' ) );
		add_action( 'wpshadow_guardian_quick_scan_fallback', array( __CLASS__, 'execute_background_diagnostics_cron' ) );
	}

	/**
	 * Execute background-safe diagnostics
	 *
	 * Runs during WordPress heartbeat for quick, low-impact diagnostics.
	 * Respects execution time limits and server load conditions.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Execution results.
	 *
	 *     @type int   $executed         Number of diagnostics executed.
	 *     @type int   $findings_count   Number of findings detected.
	 *     @type int   $execution_time   Execution time in milliseconds.
	 *     @type array $diagnostics_run  Array of diagnostic slugs executed.
	 *     @type array $findings         Array of findings detected.
	 * }
	 */
	public static function execute_background_diagnostics(): array {
		$start_time = microtime( true );

		// Check if Guardian is enabled
		if ( ! self::is_guardian_enabled() ) {
			return self::empty_result();
		}

		// Check if heartbeat execution is enabled
		if ( ! self::is_heartbeat_execution_enabled() ) {
			return self::empty_result();
		}

		// Check server load
		if ( ! self::is_server_load_acceptable() ) {
			return self::empty_result( 'Server load too high' );
		}

		// Get background-safe diagnostics that are due for execution
		$diagnostics_to_run = self::get_background_diagnostics_due();

		if ( empty( $diagnostics_to_run ) ) {
			return self::empty_result( 'No diagnostics due' );
		}

		// Execute diagnostics with time limit
		$max_time_ms = self::get_max_heartbeat_time();
		$result      = self::batch_execute( $diagnostics_to_run, $max_time_ms );

		$execution_time = round( ( microtime( true ) - $start_time ) * 1000 );

		// Log execution
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			Activity_Logger::log(
				'guardian_execution',
				sprintf(
					/* translators: %d: number of diagnostics */
					__( 'Guardian executed %d background diagnostics', 'wpshadow' ),
					$result['executed']
				),
				'guardian',
				array(
					'diagnostics_run'   => $result['diagnostics_run'],
					'execution_time_ms' => $execution_time,
					'findings_count'    => $result['findings_count'],
					'trigger'           => 'heartbeat',
				)
			);
		}

		$result['execution_time'] = $execution_time;

		return $result;
	}

	/**
	 * Execute scheduled (deep scan) diagnostics
	 *
	 * Runs during off-peak hours for resource-intensive diagnostics.
	 * Scheduled via WP-Cron.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Execution results.
	 *
	 *     @type int   $executed         Number of diagnostics executed.
	 *     @type int   $findings_count   Number of findings detected.
	 *     @type int   $execution_time   Execution time in milliseconds.
	 *     @type array $diagnostics_run  Array of diagnostic slugs executed.
	 *     @type array $findings         Array of findings detected.
	 * }
	 */
	public static function execute_scheduled_diagnostics(): array {
		$start_time = microtime( true );

		// Check if Guardian is enabled
		if ( ! self::is_guardian_enabled() ) {
			return self::empty_result();
		}

		// Check if deep scan is enabled
		if ( ! self::is_deep_scan_enabled() ) {
			return self::empty_result();
		}

		// Verify we're in off-peak hours
		if ( ! self::is_off_peak_time() ) {
			return self::empty_result( 'Not off-peak hours' );
		}

		// Get scheduled diagnostics that are due
		$diagnostics_to_run = self::get_scheduled_diagnostics_due();

		if ( empty( $diagnostics_to_run ) ) {
			return self::empty_result( 'No diagnostics due' );
		}

		// Execute diagnostics with generous time limit (300 seconds)
		$result = self::batch_execute( $diagnostics_to_run, 300000 );

		$execution_time = round( ( microtime( true ) - $start_time ) * 1000 );

		// Log execution
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			Activity_Logger::log(
				'guardian_deep_scan',
				sprintf(
					/* translators: %d: number of diagnostics */
					__( 'Guardian executed %d scheduled diagnostics', 'wpshadow' ),
					$result['executed']
				),
				'guardian',
				array(
					'diagnostics_run'   => $result['diagnostics_run'],
					'execution_time_ms' => $execution_time,
					'findings_count'    => $result['findings_count'],
					'trigger'           => 'scheduled',
				)
			);
		}

		// Email report if enabled
		if ( self::should_email_report( $result ) ) {
			self::send_email_report( $result );
		}

		$result['execution_time'] = $execution_time;

		return $result;
	}

	/**
	 * Execute background diagnostics via WP-Cron fallback
	 *
	 * For low-traffic sites where heartbeat doesn't fire frequently.
	 *
	 * @since  1.2601.2148
	 * @return array Execution results.
	 */
	public static function execute_background_diagnostics_cron(): array {
		// Reuse the background execution logic
		$result = self::execute_background_diagnostics();

		// Update trigger in log
		if ( isset( $result['trigger'] ) ) {
			$result['trigger'] = 'cron_fallback';
		}

		return $result;
	}

	/**
	 * Batch execute diagnostics with time limit
	 *
	 * @since  1.2601.2148
	 * @param  array $diagnostic_slugs Array of diagnostic slugs to execute.
	 * @param  int   $max_time_ms      Maximum execution time in milliseconds.
	 * @return array {
	 *     Execution results.
	 *
	 *     @type int   $executed         Number of diagnostics executed.
	 *     @type int   $findings_count   Number of findings detected.
	 *     @type array $diagnostics_run  Array of diagnostic slugs executed.
	 *     @type array $findings         Array of findings detected.
	 * }
	 */
	protected static function batch_execute( array $diagnostic_slugs, int $max_time_ms ): array {
		$start_time      = microtime( true );
		$executed        = 0;
		$findings        = array();
		$diagnostics_run = array();

		foreach ( $diagnostic_slugs as $slug ) {
			// Check if we're approaching time limit
			$elapsed_ms = ( microtime( true ) - $start_time ) * 1000;
			if ( $elapsed_ms >= $max_time_ms * 0.9 ) { // 90% threshold
				break;
			}

			// Execute diagnostic
			$finding = self::execute_diagnostic( $slug );

			if ( null !== $finding ) {
				$findings[] = $finding;
			}

			$diagnostics_run[] = $slug;
			++$executed;

			// Record execution time
			Diagnostic_Scheduler::record_run( $slug );
		}

		return array(
			'executed'        => $executed,
			'findings_count'  => count( $findings ),
			'diagnostics_run' => $diagnostics_run,
			'findings'        => $findings,
		);
	}

	/**
	 * Execute a single diagnostic
	 *
	 * @since  1.2601.2148
	 * @param  string $slug Diagnostic slug.
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	protected static function execute_diagnostic( string $slug ): ?array {
		// Get diagnostic class from registry
		$diagnostics = Diagnostic_Registry::get_diagnostics();
		$class_name  = $diagnostics[ $slug ] ?? null;

		if ( ! $class_name ) {
			return null;
		}

		// Build full class name with namespace
		$full_class_name = 'WPShadow\\Diagnostics\\' . $class_name;

		if ( ! class_exists( $full_class_name ) || ! method_exists( $full_class_name, 'execute' ) ) {
			return null;
		}

		try {
			// Execute diagnostic
			$finding = call_user_func( array( $full_class_name, 'execute' ) );

			// Track KPI if finding detected
			if ( null !== $finding && class_exists( 'WPShadow\Core\KPI_Tracker' ) ) {
				$severity = $finding['severity'] ?? 'medium';
				KPI_Tracker::log_finding_detected( $slug, $severity, 'guardian_auto' );
			}

			return $finding;
		} catch ( \Exception $e ) {
			// Log error but don't fail
			if ( class_exists( 'WPShadow\Core\Error_Handler' ) ) {
				Error_Handler::log_error(
					sprintf( 'Guardian diagnostic execution failed: %s', $slug ),
					$e
				);
			}
			return null;
		}
	}

	/**
	 * Get background-safe diagnostics that are due for execution
	 *
	 * @since  1.2601.2148
	 * @return array Array of diagnostic slugs.
	 */
	protected static function get_background_diagnostics_due(): array {
		// Get all diagnostics from registry
		$all_diagnostics = Diagnostic_Registry::get_diagnostics();
		$due_diagnostics = array();
		$count           = 0;

		foreach ( array_keys( $all_diagnostics ) as $slug ) {
			// Check if diagnostic is background-safe
			if ( ! self::is_background_safe( $slug ) ) {
				continue;
			}

			// Check if diagnostic should run now
			if ( ! Diagnostic_Scheduler::should_run( $slug ) ) {
				continue;
			}

			$due_diagnostics[] = $slug;
			++$count;

			// Limit to max per heartbeat
			if ( $count >= self::MAX_DIAGNOSTICS_PER_HEARTBEAT ) {
				break;
			}
		}

		return $due_diagnostics;
	}

	/**
	 * Get scheduled diagnostics that are due for execution
	 *
	 * @since  1.2601.2148
	 * @return array Array of diagnostic slugs.
	 */
	protected static function get_scheduled_diagnostics_due(): array {
		// Get all diagnostics from registry
		$all_diagnostics = Diagnostic_Registry::get_diagnostics();
		$due_diagnostics = array();

		foreach ( array_keys( $all_diagnostics ) as $slug ) {
			// Check if diagnostic is scheduled-only
			if ( ! self::is_scheduled_only( $slug ) ) {
				continue;
			}

			// Check if diagnostic should run now
			if ( ! Diagnostic_Scheduler::should_run( $slug ) ) {
				continue;
			}

			$due_diagnostics[] = $slug;
		}

		return $due_diagnostics;
	}

	/**
	 * Check if diagnostic is background-safe
	 *
	 * @since  1.2601.2148
	 * @param  string $slug Diagnostic slug.
	 * @return bool True if diagnostic can run in background.
	 */
	protected static function is_background_safe( string $slug ): bool {
		$impact = Performance_Impact_Classifier::predict( $slug );

		// Check guardian classification
		$guardian = $impact['guardian_level'] ?? '';
		if ( $guardian === Performance_Impact_Classifier::GUARDIAN_ANYTIME ||
			 $guardian === Performance_Impact_Classifier::GUARDIAN_BACKGROUND ) {
			return true;
		}

		// Fallback: check impact level
		$impact_level = $impact['impact_level'] ?? '';
		return in_array(
			$impact_level,
			array(
				Performance_Impact_Classifier::IMPACT_NONE,
				Performance_Impact_Classifier::IMPACT_MINIMAL,
				Performance_Impact_Classifier::IMPACT_LOW,
				Performance_Impact_Classifier::IMPACT_MEDIUM,
			),
			true
		);
	}

	/**
	 * Check if diagnostic is scheduled-only
	 *
	 * @since  1.2601.2148
	 * @param  string $slug Diagnostic slug.
	 * @return bool True if diagnostic should only run during scheduled times.
	 */
	protected static function is_scheduled_only( string $slug ): bool {
		$impact = Performance_Impact_Classifier::predict( $slug );

		// Check guardian classification
		$guardian = $impact['guardian_level'] ?? '';
		return $guardian === Performance_Impact_Classifier::GUARDIAN_SCHEDULED ||
			   $guardian === Performance_Impact_Classifier::GUARDIAN_MANUAL;
	}

	/**
	 * Check if current time is within off-peak hours
	 *
	 * @since  1.2601.2148
	 * @return bool True if current time is off-peak.
	 */
	public static function is_off_peak_time(): bool {
		$current_hour = (int) current_time( 'G' ); // 24-hour format
		$off_peak_hours = self::get_off_peak_hours();

		return in_array( $current_hour, $off_peak_hours, true );
	}

	/**
	 * Get off-peak hours configuration
	 *
	 * @since  1.2601.2148
	 * @return array Array of hours (0-23).
	 */
	public static function get_off_peak_hours(): array {
		$hours = get_option( 'wpshadow_guardian_off_peak_hours', self::DEFAULT_OFF_PEAK_HOURS );

		if ( ! is_array( $hours ) || empty( $hours ) ) {
			return self::DEFAULT_OFF_PEAK_HOURS;
		}

		return array_map( 'intval', $hours );
	}

	/**
	 * Check if server load is acceptable
	 *
	 * @since  1.2601.2148
	 * @return bool True if server load is acceptable for Guardian execution.
	 */
	protected static function is_server_load_acceptable(): bool {
		// If we can't determine load, assume acceptable
		if ( ! function_exists( 'sys_getloadavg' ) ) {
			return true;
		}

		$load = sys_getloadavg();
		if ( ! is_array( $load ) || ! isset( $load[0] ) ) {
			return true;
		}

		// Get CPU count (default to 1 if can't determine)
		$cpu_count = 1;
		if ( is_file( '/proc/cpuinfo' ) ) {
			$cpuinfo = file_get_contents( '/proc/cpuinfo' );
			preg_match_all( '/^processor/m', $cpuinfo, $matches );
			$cpu_count = count( $matches[0] );
		}

		// Calculate load percentage
		$load_percent = ( $load[0] / $cpu_count ) * 100;

		return $load_percent < self::MAX_SERVER_LOAD_PERCENT;
	}

	/**
	 * Check if Guardian is enabled
	 *
	 * @since  1.2601.2148
	 * @return bool True if Guardian is enabled.
	 */
	protected static function is_guardian_enabled(): bool {
		return (bool) get_option( 'wpshadow_guardian_enabled', true );
	}

	/**
	 * Check if heartbeat execution is enabled
	 *
	 * @since  1.2601.2148
	 * @return bool True if heartbeat execution is enabled.
	 */
	protected static function is_heartbeat_execution_enabled(): bool {
		return (bool) get_option( 'wpshadow_guardian_heartbeat_enabled', true );
	}

	/**
	 * Check if deep scan is enabled
	 *
	 * @since  1.2601.2148
	 * @return bool True if deep scan is enabled.
	 */
	protected static function is_deep_scan_enabled(): bool {
		return (bool) get_option( 'wpshadow_guardian_deep_scan_enabled', true );
	}

	/**
	 * Get maximum heartbeat execution time
	 *
	 * @since  1.2601.2148
	 * @return int Maximum time in milliseconds.
	 */
	protected static function get_max_heartbeat_time(): int {
		$max_ms = (int) get_option( 'wpshadow_guardian_max_heartbeat_ms', self::MAX_HEARTBEAT_MS );
		return max( 50, min( $max_ms, 500 ) ); // Clamp between 50-500ms
	}

	/**
	 * Check if email report should be sent
	 *
	 * @since  1.2601.2148
	 * @param  array $result Execution result.
	 * @return bool True if email should be sent.
	 */
	protected static function should_email_report( array $result ): bool {
		if ( ! get_option( 'wpshadow_guardian_email_reports', false ) ) {
			return false;
		}

		// Only send if findings were detected
		return ! empty( $result['findings'] );
	}

	/**
	 * Send email report
	 *
	 * @since  1.2601.2148
	 * @param  array $result Execution result.
	 * @return void
	 */
	protected static function send_email_report( array $result ): void {
		$to = get_option( 'wpshadow_guardian_email_address', get_option( 'admin_email' ) );

		$subject = sprintf(
			/* translators: 1: site name, 2: findings count */
			__( '[%1$s] Guardian Report: %2$d Issues Detected', 'wpshadow' ),
			get_bloginfo( 'name' ),
			$result['findings_count']
		);

		$message = self::format_email_report( $result );

		wp_mail( $to, $subject, $message );
	}

	/**
	 * Format email report
	 *
	 * @since  1.2601.2148
	 * @param  array $result Execution result.
	 * @return string Formatted email message.
	 */
	protected static function format_email_report( array $result ): string {
		$message = sprintf(
			/* translators: 1: site name, 2: findings count */
			__( 'WPShadow Guardian detected %2$d issues on %1$s:', 'wpshadow' ) . "\n\n",
			get_bloginfo( 'name' ),
			$result['findings_count']
		);

		foreach ( $result['findings'] as $finding ) {
			$title    = $finding['title'] ?? __( 'Unknown Issue', 'wpshadow' );
			$severity = $finding['severity'] ?? 'medium';
			$message .= sprintf( "- [%s] %s\n", strtoupper( $severity ), $title );
		}

		$message .= "\n" . sprintf(
			/* translators: %s: dashboard URL */
			__( 'View details: %s', 'wpshadow' ),
			admin_url( 'admin.php?page=wpshadow' )
		);

		return $message;
	}

	/**
	 * Get empty result array
	 *
	 * @since  1.2601.2148
	 * @param  string $reason Optional reason for empty result.
	 * @return array Empty result array.
	 */
	protected static function empty_result( string $reason = '' ): array {
		return array(
			'executed'        => 0,
			'findings_count'  => 0,
			'execution_time'  => 0,
			'diagnostics_run' => array(),
			'findings'        => array(),
			'reason'          => $reason,
		);
	}
}
