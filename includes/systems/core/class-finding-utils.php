<?php
/**
 * Finding / Diagnostic State Utilities
 *
 * Lightweight global helpers used by dashboard and scan handlers to persist
 * diagnostic result state between runs.
 *
 * @package ThisIsMyURL\Shadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'thisismyurl_shadow_get_diagnostic_result_ttl' ) ) {
	/**
	 * Resolve result cache TTL for a diagnostic class.
	 *
	 * @param string $class_name Fully-qualified diagnostic class name.
	 * @return int TTL in seconds.
	 */
	function thisismyurl_shadow_get_diagnostic_result_ttl( string $class_name ): int {
		$default_ttl = (int) get_option( 'thisismyurl_shadow_cache_duration', HOUR_IN_SECONDS );
		if ( $default_ttl <= 0 ) {
			$default_ttl = HOUR_IN_SECONDS;
		}

		// Keep states available for at least a day so recent runs do not instantly
		// fall back to "pending" between dashboard visits.
		$ttl = max( DAY_IN_SECONDS, $default_ttl );

		if ( defined( $class_name . '::RESULT_TTL' ) ) {
			$ttl = (int) constant( $class_name . '::RESULT_TTL' );
		} elseif ( defined( $class_name . '::CACHE_TTL' ) ) {
			$ttl = (int) constant( $class_name . '::CACHE_TTL' );
		}

		// Prefer scheduler frequency when available so cache lifetime matches the
		// intended recheck cadence (daily/weekly/monthly).
		if ( class_exists( '\\ThisIsMyURL\\Shadow\\Core\\Diagnostic_Scheduler' ) && method_exists( '\\ThisIsMyURL\\Shadow\\Core\\Diagnostic_Scheduler', 'get_schedule' ) ) {
			$run_key = '';
			if ( class_exists( $class_name ) && method_exists( $class_name, 'get_run_key' ) ) {
				$run_key = (string) call_user_func( array( $class_name, 'get_run_key' ) );
			}

			if ( '' !== $run_key ) {
				$schedule = \ThisIsMyURL\Shadow\Core\Diagnostic_Scheduler::get_schedule( $run_key );
				if ( is_array( $schedule ) && isset( $schedule['frequency'] ) ) {
					$freq = (int) $schedule['frequency'];
					if ( $freq > 0 ) {
						$ttl = max( $ttl, $freq );
					}
				}
			}
		}

		$ttl = max( 5 * MINUTE_IN_SECONDS, $ttl );
		$ttl = min( 30 * DAY_IN_SECONDS, $ttl );

		/**
		 * Filters diagnostic result TTL.
		 *
		 * @param int    $ttl        TTL in seconds.
		 * @param string $class_name Fully-qualified diagnostic class name.
		 */
		return (int) apply_filters( 'thisismyurl_shadow_diagnostic_result_ttl', $ttl, $class_name );
	}
}

if ( ! function_exists( 'thisismyurl_shadow_get_diagnostic_test_states' ) ) {
	/**
	 * Get all persisted diagnostic test states.
	 *
	 * @return array<string, array<string, mixed>> States keyed by class name.
	 */
	function thisismyurl_shadow_get_diagnostic_test_states(): array {
		$stored = get_option( 'thisismyurl_shadow_diagnostic_test_states', array() );
		return is_array( $stored ) ? $stored : array();
	}
}

if ( ! function_exists( 'thisismyurl_shadow_get_diagnostic_state_transient_key' ) ) {
	/**
	 * Build transient key for a diagnostic state.
	 *
	 * @param string $class_name Fully-qualified diagnostic class name.
	 * @return string Transient key.
	 */
	function thisismyurl_shadow_get_diagnostic_state_transient_key( string $class_name ): string {
		return 'thisismyurl_shadow_diag_state_' . md5( $class_name );
	}
}

if ( ! function_exists( 'thisismyurl_shadow_get_valid_diagnostic_test_state' ) ) {
	/**
	 * Get a valid (non-expired) diagnostic test state.
	 *
	 * @param string $class_name Fully-qualified diagnostic class name.
	 * @param int    $timestamp  Optional. Reference timestamp. Defaults to now.
	 * @return array<string, mixed>|null Valid state when present, null otherwise.
	 */
	function thisismyurl_shadow_get_valid_diagnostic_test_state( string $class_name, int $timestamp = 0 ): ?array {
		$transient_key = thisismyurl_shadow_get_diagnostic_state_transient_key( $class_name );
		$cached_state  = get_transient( $transient_key );

		if ( is_array( $cached_state ) ) {
			$status = isset( $cached_state['status'] ) ? (string) $cached_state['status'] : 'unknown';
			if ( 'passed' === $status || 'failed' === $status ) {
				return $cached_state;
			}
		}

		$states = thisismyurl_shadow_get_diagnostic_test_states();
		if ( ! isset( $states[ $class_name ] ) || ! is_array( $states[ $class_name ] ) ) {
			return null;
		}

		$state      = $states[ $class_name ];
		$now        = $timestamp > 0 ? $timestamp : time();
		$expires_at = isset( $state['expires_at'] ) ? (int) $state['expires_at'] : 0;
		$status     = isset( $state['status'] ) ? (string) $state['status'] : 'unknown';

		if ( $expires_at <= 0 || $expires_at < $now ) {
			return null;
		}

		if ( 'passed' !== $status && 'failed' !== $status ) {
			return null;
		}

		return $state;
	}
}

if ( ! function_exists( 'thisismyurl_shadow_record_diagnostic_test_states' ) ) {
	/**
	 * Persist diagnostic test states after a scan run.
	 *
	 * @param array<string, array<string, mixed>> $results   Diagnostic results keyed by class name.
	 * @param int                                 $timestamp Optional. Checked timestamp. Defaults to now.
	 * @return array<string, array<string, mixed>> Updated state map.
	 */
	function thisismyurl_shadow_record_diagnostic_test_states( array $results, int $timestamp = 0 ): array {
		$checked_at = $timestamp > 0 ? $timestamp : time();
		$states     = thisismyurl_shadow_get_diagnostic_test_states();

		foreach ( $results as $class_name => $result ) {
			if ( ! is_string( $class_name ) || '' === $class_name || ! is_array( $result ) ) {
				continue;
			}

			$status = isset( $result['status'] ) ? (string) $result['status'] : '';
			if ( 'passed' !== $status && 'failed' !== $status ) {
				continue;
			}

			$ttl = isset( $result['ttl'] ) ? (int) $result['ttl'] : thisismyurl_shadow_get_diagnostic_result_ttl( $class_name );
			$ttl = max( 5 * MINUTE_IN_SECONDS, min( WEEK_IN_SECONDS, $ttl ) );

			$state = array(
				'status'     => $status,
				'category'   => isset( $result['category'] ) ? sanitize_key( (string) $result['category'] ) : '',
				'finding_id' => isset( $result['finding_id'] ) ? sanitize_key( (string) $result['finding_id'] ) : '',
				'checked_at' => $checked_at,
				'expires_at' => $checked_at + $ttl,
				'ttl'        => $ttl,
			);

			$states[ $class_name ] = $state;
			set_transient( thisismyurl_shadow_get_diagnostic_state_transient_key( $class_name ), $state, $ttl );
		}

		update_option( 'thisismyurl_shadow_diagnostic_test_states', $states );
		return $states;
	}
}

if ( ! function_exists( 'thisismyurl_shadow_record_diagnostic_run_coverage' ) ) {
	/**
	 * Persist a lightweight run coverage snapshot.
	 *
	 * @param array<int, string> $executed_diagnostics Diagnostics that completed successfully.
	 * @param int                $timestamp Optional. Coverage timestamp. Default 0.
	 * @return array<string, mixed> Coverage data.
	 */
	function thisismyurl_shadow_record_diagnostic_run_coverage( array $executed_diagnostics, int $timestamp = 0 ): array {
		$recorded_at = $timestamp > 0 ? $timestamp : time();
		$executed    = array_values( array_unique( array_filter( array_map( 'strval', $executed_diagnostics ) ) ) );

		$total = 0;
		if ( class_exists( '\\ThisIsMyURL\\Shadow\\Diagnostics\\Diagnostic_Registry' ) ) {
			$defs  = \ThisIsMyURL\Shadow\Diagnostics\Diagnostic_Registry::get_diagnostic_definitions();
			$total = is_array( $defs ) ? count( $defs ) : 0;
		}

		$coverage = array(
			'timestamp' => $recorded_at,
			'run'       => count( $executed ),
			'total'     => max( 0, $total ),
			'classes'   => $executed,
		);

		update_option( 'thisismyurl_shadow_diagnostic_run_coverage', $coverage );
		return $coverage;
	}
}
