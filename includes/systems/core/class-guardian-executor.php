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
 * @since 0.6093.1200
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
 * @since 0.6093.1200
 */
class Guardian_Executor {

	/**
	 * Queue pointer option key.
	 *
	 * @var string
	 */
	const HEARTBEAT_QUEUE_POINTER_OPTION = 'wpshadow_heartbeat_queue_pointer';

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
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function init(): void {
		// Hook into scheduled events.
		add_action( 'wpshadow_guardian_deep_scan', array( __CLASS__, 'execute_scheduled_diagnostics' ) );
		add_action( 'wpshadow_guardian_quick_scan_fallback', array( __CLASS__, 'execute_background_diagnostics_cron' ) );
	}

	/**
	 * Execute background-safe diagnostics
	 *
	 * Runs during WordPress heartbeat for quick, low-impact diagnostics.
	 * Respects execution time limits and server load conditions.
	 *
	 * @since 0.6093.1200
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

		// Check if Guardian is enabled.
		if ( !
		self::is_guardian_enabled() ) {
			return self::empty_result();
		}

		// Ensure registries are loaded - critical for AJAX/heartbeat contexts.
		self::ensure_registries_loaded();

		// Check if heartbeat execution is enabled.
		if ( !
		self::is_heartbeat_execution_enabled() ) {
			return self::empty_result();
		}

		// Check server load.
		if ( !
		self::is_server_load_acceptable() ) {
			return self::empty_result( 'Server load too high' );
		}

		// Get background-safe diagnostics that are due for execution.
		$diagnostics_to_run = self::get_background_diagnostics_due();

		if ( empty( $diagnostics_to_run ) ) {
			return self::empty_result( 'No diagnostics due' );
		}

		// Execute diagnostics with time limit.
		$max_time_ms = self::get_max_heartbeat_time();
		$result      = self::batch_execute( $diagnostics_to_run, $max_time_ms );

		$execution_time = round( ( microtime( true ) - $start_time ) * 1000 );

		// Log execution.
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
	 * Ensure diagnostic and treatment registries are loaded
	 *
	 * Handles initialization for AJAX/heartbeat contexts where plugins_loaded
	 * hook may not have properly initialized registries.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	protected static function ensure_registries_loaded(): void {
		// Initialize Diagnostic_Registry if not already done.
		if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			\WPShadow\Diagnostics\Diagnostic_Registry::init();
		}

		// Initialize Treatment_Registry if not already done.
		if ( class_exists( '\WPShadow\Treatments\Treatment_Registry' ) ) {
			\WPShadow\Treatments\Treatment_Registry::init();
		}
	}

	/**
	 * Execute scheduled (deep scan) diagnostics
	 *
	 * Runs during off-peak hours for resource-intensive diagnostics.
	 * Scheduled via WP-Cron.
	 *
	 * @since 0.6093.1200
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

		// Check if Guardian is enabled.
		if ( !
		self::is_guardian_enabled() ) {
			return self::empty_result();
		}

		// Check if deep scan is enabled.
		if ( !
		self::is_deep_scan_enabled() ) {
			return self::empty_result();
		}

		// Verify we're in off-peak hours.
		if ( !
		self::is_off_peak_time() ) {
			return self::empty_result( 'Not off-peak hours' );
		}

		// Get scheduled diagnostics that are due.
		$diagnostics_to_run = self::get_scheduled_diagnostics_due();

		if ( empty( $diagnostics_to_run ) ) {
			return self::empty_result( 'No diagnostics due' );
		}

		// Execute diagnostics with generous time limit (300 seconds).
		$result = self::batch_execute( $diagnostics_to_run, 300000 );

		$execution_time = round( ( microtime( true ) - $start_time ) * 1000 );

		// Log execution.
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

		// Email report if enabled.
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
	 * @since 0.6093.1200
	 * @return array Execution results.
	 */
	public static function execute_background_diagnostics_cron(): array {
		// Reuse the background execution logic.
		$result = self::execute_background_diagnostics();

		// Update trigger in log.
		if ( isset( $result['trigger'] ) ) {
			$result['trigger'] = 'cron_fallback';
		}

		return $result;
	}

	/**
	 * Batch execute diagnostics with time limit
	 *
	 * @since 0.6093.1200
	 * @param  array $diagnostic_classes Array of diagnostic classes to execute.
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
	protected static function batch_execute( array $diagnostic_classes, int $max_time_ms ): array {
		$start_time      = microtime( true );
		$executed        = 0;
		$findings        = array();
		$resolved_ids    = array();
		$diagnostics_run = array();
		$results         = array();
		$completed_at    = time();
		$disabled        = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		if ( ! is_array( $disabled ) ) {
			$disabled = array();
		}

		foreach ( $diagnostic_classes as $class_name ) {
			// Check if we're approaching time limit.
			$elapsed_ms = ( microtime( true ) - $start_time ) * 1000;
			if ( $elapsed_ms >= $max_time_ms * 0.9 ) { // 90% threshold
				break;
			}

			$qualified_class = self::normalize_diagnostic_class_name( (string) $class_name );
			if ( '' === $qualified_class ) {
				continue;
			}

			$short_name = str_replace( 'WPShadow\\Diagnostics\\', '', $qualified_class );
			if ( in_array( $qualified_class, $disabled, true ) || in_array( $short_name, $disabled, true ) ) {
				continue;
			}

			$cached_state = function_exists( 'wpshadow_get_valid_diagnostic_test_state' )
				? \wpshadow_get_valid_diagnostic_test_state( $qualified_class, $completed_at )
				: null;
			if ( is_array( $cached_state ) ) {
				$results[ $qualified_class ] = array(
					'status'     => (string) ( $cached_state['status'] ?? 'unknown' ),
					'category'   => (string) ( $cached_state['category'] ?? '' ),
					'finding_id' => (string) ( $cached_state['finding_id'] ?? '' ),
				);
				continue;
			}

			// Execute diagnostic.
			$finding = self::execute_diagnostic( $qualified_class );

			$results[ $qualified_class ] = array(
				'status'     => is_array( $finding ) ? 'failed' : 'passed',
				'category'   => is_array( $finding ) ? (string) ( $finding['category'] ?? '' ) : '',
				'finding_id' => is_array( $finding ) ? (string) ( $finding['id'] ?? '' ) : self::get_diagnostic_finding_id_hint( $qualified_class ),
			);

			if ( null !== $finding ) {
				$findings[] = $finding;
			} else {
				$finding_id_hint = (string) ( $results[ $qualified_class ]['finding_id'] ?? '' );
				if ( '' !== $finding_id_hint ) {
					$resolved_ids[] = sanitize_key( $finding_id_hint );
				}
			}

			$diagnostics_run[] = $qualified_class;
			++$executed;

			// Record execution time.
			Diagnostic_Scheduler::record_run( self::get_diagnostic_run_key( $qualified_class ) );
		}

		if ( function_exists( 'wpshadow_record_diagnostic_test_states' ) ) {
			\wpshadow_record_diagnostic_test_states( $results, $completed_at );
		}

		self::persist_heartbeat_findings( $findings, $resolved_ids );

		return array(
			'executed'        => $executed,
			'findings_count'  => count( $findings ),
			'diagnostics_run' => $diagnostics_run,
			'findings'        => $findings,
		);
	}

	/**
	 * Persist heartbeat findings into the dashboard snapshot.
	 *
	 * Merges newly failed findings into `wpshadow_site_findings` and removes
	 * resolved finding IDs for diagnostics that now pass.
	 *
	 * @since 0.6093.1200
	 * @param array<int, array<string, mixed>> $new_findings Newly detected findings.
	 * @param array<int, string>               $resolved_ids Finding IDs that now pass.
	 * @return void
	 */
	protected static function persist_heartbeat_findings( array $new_findings, array $resolved_ids ): void {
		if ( ! function_exists( 'wpshadow_index_findings_by_id' ) ) {
			return;
		}

		$existing_findings = get_option( 'wpshadow_site_findings', array() );
		if ( ! is_array( $existing_findings ) ) {
			$existing_findings = array();
		}

		$indexed_findings = \wpshadow_index_findings_by_id( $existing_findings );

		foreach ( $resolved_ids as $resolved_id ) {
			$resolved_id = sanitize_key( (string) $resolved_id );
			if ( '' !== $resolved_id && isset( $indexed_findings[ $resolved_id ] ) ) {
				unset( $indexed_findings[ $resolved_id ] );
			}
		}

		$indexed_new = \wpshadow_index_findings_by_id( $new_findings );
		foreach ( $indexed_new as $finding_id => $finding ) {
			$indexed_findings[ $finding_id ] = $finding;
		}

		$merged_findings = array_values( $indexed_findings );

		if ( function_exists( 'wpshadow_store_gauge_snapshot' ) ) {
			\wpshadow_store_gauge_snapshot( $merged_findings );
		} else {
			update_option( 'wpshadow_site_findings', \wpshadow_index_findings_by_id( $merged_findings ), false );
		}
	}

	/**
	 * Get a best-effort finding ID hint for a diagnostic class.
	 *
	 * @since 0.6093.1200
	 * @param  string $qualified_class Fully-qualified diagnostic class name.
	 * @return string Finding ID hint.
	 */
	protected static function get_diagnostic_finding_id_hint( string $qualified_class ): string {
		if ( class_exists( $qualified_class ) && method_exists( $qualified_class, 'get_slug' ) ) {
			return sanitize_key( (string) call_user_func( array( $qualified_class, 'get_slug' ) ) );
		}

		return sanitize_key( self::get_diagnostic_run_key( $qualified_class ) );
	}

	/**
	 * Execute a single diagnostic
	 *
	 * @since 0.6093.1200
	 * @param  string $class_name Diagnostic class name.
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	protected static function execute_diagnostic( string $class_name ): ?array {
		if ( '' === $class_name ) {
			return null;
		}

		$full_class_name = self::normalize_diagnostic_class_name( $class_name );
		if ( '' === $full_class_name ) {
			return null;
		}

		if ( ! class_exists( $full_class_name ) ) {
			$map        = Diagnostic_Registry::get_diagnostic_file_map();
			$short_name = str_replace( 'WPShadow\\Diagnostics\\', '', $full_class_name );
			$file       = $map[ $short_name ]['file'] ?? $map[ $full_class_name ]['file'] ?? '';
			if ( is_string( $file ) && '' !== $file && file_exists( $file ) ) {
				require_once $file;
			}
		}

		if ( ! class_exists( $full_class_name ) || ! method_exists( $full_class_name, 'execute' ) ) {
			return null;
		}

		try {
			$start_time = microtime( true );

			// Execute diagnostic.
			$finding = call_user_func( array( $full_class_name, 'execute' ) );

			if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
				Activity_Logger::log(
					'diagnostic_run',
					sprintf(
						/* translators: %s: diagnostic slug */
						__( 'Checked diagnostic: %s', 'wpshadow' ),
						$full_class_name
					),
					'guardian',
					array(
						'diagnostic'       => $full_class_name,
						'trigger'          => 'heartbeat',
						'execution_ms'     => (int) round( ( microtime( true ) - $start_time ) * 1000 ),
						'finding_detected' => null !== $finding,
					)
				);
			}

			// Track KPI if finding detected.
			if ( null !== $finding && class_exists( 'WPShadow\Core\KPI_Tracker' ) ) {
				$severity = $finding['severity'] ?? 'medium';
				KPI_Tracker::log_finding_detected( $full_class_name, $severity, 'guardian_auto' );
			}

			if ( null !== $finding && class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
				Activity_Logger::log(
					'diagnostic_finding',
					sprintf(
						/* translators: %s: finding title */
						__( 'Found issue: %s', 'wpshadow' ),
						$finding['title'] ?? $full_class_name
					),
					$finding['category'] ?? 'guardian',
					array(
						'diagnostic' => $full_class_name,
						'finding_id' => $finding['id'] ?? '',
						'trigger'    => 'heartbeat',
					)
				);
			}

			return $finding;
		} catch ( \Exception $e ) {
			if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
				Activity_Logger::log(
					'diagnostic_failed',
					sprintf(
						/* translators: 1: diagnostic slug, 2: error message */
						__( 'Diagnostic failed: %1$s (%2$s)', 'wpshadow' ),
						$full_class_name,
						$e->getMessage()
					),
					'guardian',
					array(
						'diagnostic' => $full_class_name,
						'trigger'    => 'heartbeat',
					)
				);
			}

			// Log error but don't fail.
			if ( class_exists( 'WPShadow\Core\Error_Handler' ) ) {
				Error_Handler::log_error(
					sprintf( 'Guardian diagnostic execution failed: %s', $full_class_name ),
					$e
				);
			}
			return null;
		}
	}

	/**
	 * Get background-safe diagnostics that are due for execution
	 *
	 * @since 0.6093.1200
	 * @return array Array of diagnostic slugs.
	 */
	protected static function get_background_diagnostics_due(): array {
		return self::get_next_heartbeat_queue_batch( self::MAX_DIAGNOSTICS_PER_HEARTBEAT );
	}

	/**
	 * Get next diagnostics batch from the persisted heartbeat queue.
	 *
	 * @param int $batch_size Number of diagnostics to return.
	 * @return array<int, string> Array of diagnostic class names.
	 */
	protected static function get_next_heartbeat_queue_batch( int $batch_size ): array {
		$all_diagnostics = Diagnostic_Registry::get_all();
		if ( ! is_array( $all_diagnostics ) || empty( $all_diagnostics ) ) {
			return array();
		}

		$queue = array_values( array_filter( array_map( array( __CLASS__, 'normalize_diagnostic_class_name' ), $all_diagnostics ) ) );
		if ( empty( $queue ) ) {
			return array();
		}

		sort( $queue, SORT_STRING );

		$queue_size = count( $queue );
		$pointer    = (int) get_option( self::HEARTBEAT_QUEUE_POINTER_OPTION, 0 );
		if ( $pointer < 0 || $pointer >= $queue_size ) {
			$pointer = 0;
		}

		$batch       = array();
		$visited     = 0;
		$checked_at  = time();
		$max_batch   = max( 1, $batch_size );
		$batch_count = 0;

		while ( $visited < $queue_size && $batch_count < $max_batch ) {
			$index     = ( $pointer + $visited ) % $queue_size;
			$candidate = (string) $queue[ $index ];

			if ( self::is_heartbeat_candidate_due( $candidate, $checked_at ) ) {
				$batch[] = $candidate;
				++$batch_count;
			}

			++$visited;
		}

		if ( $visited > 0 ) {
			$new_pointer = ( $pointer + $visited ) % $queue_size;
			update_option( self::HEARTBEAT_QUEUE_POINTER_OPTION, $new_pointer, false );
		}

		return $batch;
	}

	/**
	 * Determine whether a heartbeat queue candidate should execute now.
	 *
	 * @param string $class_name Fully-qualified diagnostic class name.
	 * @param int    $timestamp  Current timestamp.
	 * @return bool
	 */
	protected static function is_heartbeat_candidate_due( string $class_name, int $timestamp ): bool {
		if ( '' === $class_name ) {
			return false;
		}

		$run_key = self::get_diagnostic_run_key( $class_name );
		if ( ! self::is_background_safe( $run_key ) ) {
			return false;
		}

		if ( function_exists( 'wpshadow_get_valid_diagnostic_test_state' ) ) {
			$cached_state = \wpshadow_get_valid_diagnostic_test_state( $class_name, $timestamp );
			if ( is_array( $cached_state ) ) {
				return false;
			}
		}

		return Diagnostic_Scheduler::should_run( $run_key );
	}

	/**
	 * Normalize class name to fully-qualified WPShadow diagnostics class.
	 *
	 * @param string $class_name Diagnostic class name.
	 * @return string
	 */
	protected static function normalize_diagnostic_class_name( string $class_name ): string {
		$class_name = trim( $class_name );
		if ( '' === $class_name ) {
			return '';
		}

		if ( 0 === strpos( $class_name, 'WPShadow\\Diagnostics\\' ) ) {
			return $class_name;
		}

		return 'WPShadow\\Diagnostics\\' . ltrim( $class_name, '\\' );
	}

	/**
	 * Build stable key for scheduler run-record tracking.
	 *
	 * @param string $class_name Fully-qualified diagnostic class name.
	 * @return string
	 */
	protected static function get_diagnostic_run_key( string $class_name ): string {
		$short_name = str_replace( 'WPShadow\\Diagnostics\\', '', $class_name );
		$short_name = strtolower( str_replace( '_', '-', $short_name ) );
		return sanitize_key( $short_name );
	}

	/**
	 * Get scheduled diagnostics that are due for execution
	 *
	 * @since 0.6093.1200
	 * @return array Array of diagnostic slugs.
	 */
	protected static function get_scheduled_diagnostics_due(): array {
		// Get all diagnostics from registry.
		$all_diagnostics = Diagnostic_Registry::get_diagnostics();
		$due_diagnostics = array();

		foreach ( array_keys( $all_diagnostics ) as $slug ) {
			// Check if diagnostic is scheduled-only.
			if ( !
			self::is_scheduled_only( $slug ) ) {
				continue;
			}

			// Check if diagnostic should run now.
			if ( !
			Diagnostic_Scheduler::should_run( $slug ) ) {
				continue;
			}

			$due_diagnostics[] = $slug;
		}

		return $due_diagnostics;
	}

	/**
	 * Check if diagnostic is background-safe
	 *
	 * @since 0.6093.1200
	 * @param  string $slug Diagnostic slug.
	 * @return bool True if diagnostic can run in background.
	 */
	protected static function is_background_safe( string $slug ): bool {
		if ( ! self::ensure_performance_classifier_loaded() ) {
			// Fail open for heartbeat safety if classifier is unavailable.
			return true;
		}

		$impact = Performance_Impact_Classifier::predict( $slug );

		// Check guardian classification.
		$guardian = $impact['guardian_level'] ??
		'';
		if ( Performance_Impact_Classifier::GUARDIAN_ANYTIME === $guardian ||
			Performance_Impact_Classifier::GUARDIAN_BACKGROUND === $guardian ) {
			return true;
		}

		// Fallback: check impact level.
		$impact_level = $impact['impact_level'] ??
		'';
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
	 * @since 0.6093.1200
	 * @param  string $slug Diagnostic slug.
	 * @return bool True if diagnostic should only run during scheduled times.
	 */
	protected static function is_scheduled_only( string $slug ): bool {
		if ( ! self::ensure_performance_classifier_loaded() ) {
			return false;
		}

		$impact = Performance_Impact_Classifier::predict( $slug );

		// Check guardian classification.
		$guardian = $impact['guardian_level'] ??
		'';
		return Performance_Impact_Classifier::GUARDIAN_SCHEDULED === $guardian ||
				Performance_Impact_Classifier::GUARDIAN_MANUAL === $guardian;
	}

	/**
	 * Ensure the performance impact classifier is available.
	 *
	 * @return bool
	 */
	protected static function ensure_performance_classifier_loaded(): bool {
		if ( class_exists( '\\WPShadow\\Core\\Performance_Impact_Classifier' ) ) {
			return true;
		}

		$classifier_file = WPSHADOW_PATH . 'includes/systems/core/class-performance-impact-classifier.php';
		if ( file_exists( $classifier_file ) ) {
			require_once $classifier_file;
		}

		return class_exists( '\\WPShadow\\Core\\Performance_Impact_Classifier' );
	}

	/**
	 * Check if current time is within off-peak hours
	 *
	 * @since 0.6093.1200
	 * @return bool True if current time is off-peak.
	 */
	public static function is_off_peak_time(): bool {
		$current_hour   = (int) current_time( 'G' ); // 24-hour format
		$off_peak_hours = self::get_off_peak_hours();

		return in_array( $current_hour, $off_peak_hours, true );
	}

	/**
	 * Get off-peak hours configuration
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return bool True if server load is acceptable for Guardian execution.
	 */
	protected static function is_server_load_acceptable(): bool {
		// If we can't determine load, assume acceptable.
		if ( !
		function_exists( 'sys_getloadavg' ) ) {
			return true;
		}

		$load = sys_getloadavg();
		if ( ! is_array( $load ) || ! isset( $load[0] ) ) {
			return true;
		}

		// Get CPU count (default to 1 if can't determine).
		$cpu_count = 1;
		if ( is_file( '/proc/cpuinfo' ) ) {
			$cpuinfo = file_get_contents( '/proc/cpuinfo' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			preg_match_all( '/^processor/m', $cpuinfo, $matches );
			$cpu_count = count( $matches[0] );
		}

		// Calculate load percentage.
		$load_percent = ( $load[0] / $cpu_count ) * 100;

		return $load_percent < self::MAX_SERVER_LOAD_PERCENT;
	}

	/**
	 * Check if Guardian is enabled
	 *
	 * @since 0.6093.1200
	 * @return bool True if Guardian is enabled.
	 */
	protected static function is_guardian_enabled(): bool {
		return (bool) get_option( 'wpshadow_guardian_enabled', true );
	}

	/**
	 * Check if heartbeat execution is enabled
	 *
	 * @since 0.6093.1200
	 * @return bool True if heartbeat execution is enabled.
	 */
	protected static function is_heartbeat_execution_enabled(): bool {
		return (bool) get_option( 'wpshadow_guardian_heartbeat_enabled', true );
	}

	/**
	 * Check if deep scan is enabled
	 *
	 * @since 0.6093.1200
	 * @return bool True if deep scan is enabled.
	 */
	protected static function is_deep_scan_enabled(): bool {
		return (bool) get_option( 'wpshadow_guardian_deep_scan_enabled', true );
	}

	/**
	 * Get maximum heartbeat execution time
	 *
	 * @since 0.6093.1200
	 * @return int Maximum time in milliseconds.
	 */
	protected static function get_max_heartbeat_time(): int {
		$max_ms = (int) get_option( 'wpshadow_guardian_max_heartbeat_ms', self::MAX_HEARTBEAT_MS );
		return max( 50, min( $max_ms, 500 ) ); // Clamp between 50-500ms.
	}

	/**
	 * Check if email report should be sent
	 *
	 * @since 0.6093.1200
	 * @param  array $result Execution result.
	 * @return bool True if email should be sent.
	 */
	protected static function should_email_report( array $result ): bool {
		if ( ! get_option( 'wpshadow_guardian_email_reports', false ) ) {
			return false;
		}

		// Only send if findings were detected.
		return !
		empty( $result['findings'] );
	}

	/**
	 * Send email report
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
