<?php
/**
 * AJAX Handler: Bulk Run Pending Diagnostics
 *
 * Offers batched execution of diagnostics that do not currently have a valid
 * cached pass/fail state so administrators can quickly warm diagnostic coverage.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Diagnostic_Scheduler;
use WPShadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bulk Run Pending Diagnostics AJAX Handler.
 */
class Bulk_Run_Pending_Diagnostics_Handler extends AJAX_Handler_Base {

	/**
	 * Queue transient lifetime in seconds.
	 *
	 * @var int
	 */
	const QUEUE_TTL = 30 * MINUTE_IN_SECONDS;

	/**
	 * Default diagnostics per batch.
	 *
	 * @var int
	 */
	const DEFAULT_BATCH_SIZE = 15;

	/**
	 * Default batch time budget in milliseconds.
	 *
	 * @var int
	 */
	const DEFAULT_BATCH_BUDGET_MS = 2000;

	/**
	 * Register AJAX hook.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_run_pending_diagnostics', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle batched pending diagnostics execution.
	 *
	 * @return void
	 */
	public static function handle(): void {
		$buffer_level = ob_get_level();
		ob_start();

		try {
			self::verify_request( 'wpshadow_dashboard_nonce', 'manage_options' );

			if ( ! class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
				$registry_file = WPSHADOW_PATH . 'includes/systems/diagnostics/class-diagnostic-registry.php';
				if ( file_exists( $registry_file ) ) {
					require_once $registry_file;
				}
			}

			if ( ! class_exists( '\\WPShadow\\Core\\Diagnostic_Scheduler' ) ) {
				$scheduler_file = WPSHADOW_PATH . 'includes/utils/class-diagnostic-scheduler.php';
				if ( file_exists( $scheduler_file ) ) {
					require_once $scheduler_file;
				}
			}

			if ( ! class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
				self::send_error( __( 'Diagnostic registry is unavailable.', 'wpshadow' ) );
			}

			if ( ! class_exists( '\\WPShadow\\Core\\Diagnostic_Scheduler' ) ) {
				self::send_error( __( 'Diagnostic scheduler is unavailable.', 'wpshadow' ) );
			}

			$mode = self::get_post_param( 'mode', 'key', 'batch' );
			if ( 'start' !== $mode && 'batch' !== $mode && 'cancel' !== $mode && 'count' !== $mode ) {
				$mode = 'batch';
			}

			if ( 'cancel' === $mode ) {
				delete_transient( self::get_queue_key() );
				self::send_clean_success(
					$buffer_level,
					array(
						'complete'  => true,
						'cancelled' => true,
					)
				);
			}

			if ( 'count' === $mode ) {
				$state = get_transient( self::get_queue_key() );
				if ( ! is_array( $state ) || ! isset( $state['queue'] ) || ! is_array( $state['queue'] ) ) {
					$state = self::build_queue_state();
					set_transient( self::get_queue_key(), $state, self::QUEUE_TTL );
				}

				$total     = (int) ( $state['total'] ?? 0 );
				$pointer   = (int) ( $state['pointer'] ?? 0 );
				$remaining = max( 0, $total - $pointer );

				self::send_clean_success(
					$buffer_level,
					array(
						'pending_total' => $remaining,
					)
				);
			}

			$state = get_transient( self::get_queue_key() );
			if ( 'start' === $mode || ! is_array( $state ) || empty( $state['queue'] ) ) {
				$state = self::build_queue_state();
				set_transient( self::get_queue_key(), $state, self::QUEUE_TTL );
			}

			$batch_size = max( 5, min( 25, (int) self::get_post_param( 'batch_size', 'int', self::DEFAULT_BATCH_SIZE ) ) );
			$budget_ms  = max( 500, min( 5000, (int) self::get_post_param( 'budget_ms', 'int', self::DEFAULT_BATCH_BUDGET_MS ) ) );
			$result     = self::run_batch( $state, $batch_size, $budget_ms );

			if ( ! empty( $result['complete'] ) ) {
				delete_transient( self::get_queue_key() );
			} else {
				set_transient( self::get_queue_key(), $result['state'], self::QUEUE_TTL );
			}

			unset( $result['state'] );
			self::send_clean_success( $buffer_level, $result );
		} catch ( \Throwable $e ) {
			self::log_bulk_error( 'Bulk pending diagnostics handler failed', $e );

			$details = current_user_can( 'manage_options' )
				? $e->getMessage()
				: '';

			self::send_clean_error(
				$buffer_level,
				__( 'Bulk run could not start due to a server error. Please try again.', 'wpshadow' ),
				array(
					'details' => $details,
				)
			);
		}
	}

	/**
	 * Send success JSON while clearing any unexpected buffered output.
	 *
	 * @param int   $buffer_level Starting output buffer level.
	 * @param array $data         Response payload.
	 * @return void
	 */
	protected static function send_clean_success( int $buffer_level, array $data ): void {
		self::clear_unexpected_output( $buffer_level );
		self::send_success( $data );
	}

	/**
	 * Send error JSON while clearing any unexpected buffered output.
	 *
	 * @param int          $buffer_level Starting output buffer level.
	 * @param string|mixed $message      Error message.
	 * @param array        $data         Extra error data.
	 * @return void
	 */
	protected static function send_clean_error( int $buffer_level, $message, array $data = array() ): void {
		self::clear_unexpected_output( $buffer_level );
		self::send_error( $message, $data );
	}

	/**
	 * Clear stray output (e.g. notices) emitted before JSON response.
	 *
	 * @param int $buffer_level Starting output buffer level.
	 * @return void
	 */
	protected static function clear_unexpected_output( int $buffer_level ): void {
		$noise = '';
		while ( ob_get_level() > $buffer_level ) {
			$chunk = ob_get_clean();
			if ( is_string( $chunk ) ) {
				$noise .= $chunk;
			}
		}

		if ( '' !== trim( $noise ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug output from third-party/runtime notices.
			error_log( 'WPShadow bulk run stripped unexpected output: ' . wp_strip_all_tags( $noise ) );
		}
	}

	/**
	 * Execute one queue batch.
	 *
	 * @param array $state      Queue state.
	 * @param int   $batch_size Diagnostics to process in this call.
	 * @param int   $budget_ms  Time budget for this call.
	 * @return array<string, mixed>
	 */
	protected static function run_batch( array $state, int $batch_size, int $budget_ms ): array {
		$queue      = isset( $state['queue'] ) && is_array( $state['queue'] ) ? array_values( $state['queue'] ) : array();
		$total      = (int) ( $state['total'] ?? count( $queue ) );
		$pointer    = (int) ( $state['pointer'] ?? 0 );
		$file_map   = Diagnostic_Registry::get_diagnostic_file_map();
		$results    = array();
		$executed   = 0;
		$start_time = microtime( true );

		while ( $pointer < $total && $executed < $batch_size ) {
			$elapsed_ms = ( microtime( true ) - $start_time ) * 1000;
			if ( $elapsed_ms >= $budget_ms ) {
				break;
			}

			$class_name = (string) ( $queue[ $pointer ] ?? '' );
			++$pointer;

			if ( '' === $class_name ) {
				continue;
			}

			$execution = self::execute_diagnostic_class( $class_name, $file_map );
			if ( ! empty( $execution['recordable'] ) ) {
				$results[ $class_name ] = array(
					'status'     => $execution['status'],
					'category'   => $execution['category'],
					'finding_id' => $execution['finding_id'],
				);

				Diagnostic_Scheduler::record_run( self::get_run_key( $class_name ) );
				++$executed;
			}
		}

		if ( ! empty( $results ) && function_exists( 'wpshadow_record_diagnostic_test_states' ) ) {
			\wpshadow_record_diagnostic_test_states( $results, time() );
		}

		$state['pointer'] = $pointer;
		$remaining        = max( 0, $total - $pointer );
		$complete         = 0 === $remaining;
		$percent          = $total > 0 ? (int) round( ( ( $total - $remaining ) / $total ) * 100 ) : 100;

		return array(
			'total'             => $total,
			'processed'         => $total - $remaining,
			'remaining'         => $remaining,
			'executed_this_call' => $executed,
			'complete'          => $complete,
			'percent'           => $percent,
			'state'             => $state,
		);
	}

	/**
	 * Build fresh queue state from diagnostics without valid cached states.
	 *
	 * @return array<string, mixed>
	 */
	protected static function build_queue_state(): array {
		$map      = Diagnostic_Registry::get_diagnostic_file_map();
		$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		$queue    = array();
		$now      = time();

		if ( ! is_array( $disabled ) ) {
			$disabled = array();
		}

		foreach ( $map as $class_name => $data ) {
			if ( ! is_string( $class_name ) || '' === $class_name ) {
				continue;
			}

			$qualified = self::normalize_class_name( $class_name );
			$short     = str_replace( 'WPShadow\\Diagnostics\\', '', $qualified );
			$is_disabled = in_array( $qualified, $disabled, true ) || in_array( $short, $disabled, true );
			if ( $is_disabled ) {
				continue;
			}

			if ( function_exists( 'wpshadow_get_valid_diagnostic_test_state' ) ) {
				$cached = \wpshadow_get_valid_diagnostic_test_state( $qualified, $now );
				if ( is_array( $cached ) ) {
					continue;
				}
			}

			if ( ! self::is_diagnostic_executable( $qualified, $map ) ) {
				continue;
			}

			$queue[] = $qualified;
		}

		sort( $queue, SORT_STRING );

		return array(
			'queue'   => $queue,
			'total'   => count( $queue ),
			'pointer' => 0,
		);
	}

	/**
	 * Check whether a diagnostic class is executable.
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @param array  $file_map   Diagnostic file map.
	 * @return bool
	 */
	protected static function is_diagnostic_executable( string $class_name, array $file_map ): bool {
		$qualified = self::normalize_class_name( $class_name );
		if ( '' === $qualified ) {
			return false;
		}

		try {
			if ( ! class_exists( $qualified ) ) {
				$short = str_replace( 'WPShadow\\Diagnostics\\', '', $qualified );
				$file  = (string) ( $file_map[ $short ]['file'] ?? $file_map[ $qualified ]['file'] ?? '' );
				if ( '' !== $file && file_exists( $file ) ) {
					require_once $file;
				}
			}

			return class_exists( $qualified ) && method_exists( $qualified, 'execute' );
		} catch ( \Throwable $e ) {
			self::log_bulk_error( 'Bulk pending diagnostic preflight failed: ' . $qualified, $e );
			return false;
		}
	}

	/**
	 * Execute one diagnostic class and normalize its result.
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @param array  $file_map   Diagnostic file map.
	 * @return array<string, mixed>
	 */
	protected static function execute_diagnostic_class( string $class_name, array $file_map ): array {
		try {
			$qualified = self::normalize_class_name( $class_name );
			if ( '' === $qualified ) {
				return array( 'recordable' => false );
			}

			if ( ! class_exists( $qualified ) ) {
				$short = str_replace( 'WPShadow\\Diagnostics\\', '', $qualified );
				$file  = (string) ( $file_map[ $short ]['file'] ?? $file_map[ $qualified ]['file'] ?? '' );
				if ( '' !== $file && file_exists( $file ) ) {
					require_once $file;
				}
			}

			if ( ! class_exists( $qualified ) || ! method_exists( $qualified, 'execute' ) ) {
				return array( 'recordable' => false );
			}

			$finding = call_user_func( array( $qualified, 'execute' ) );
			return array(
				'recordable' => true,
				'status'     => is_array( $finding ) ? 'failed' : 'passed',
				'category'   => is_array( $finding ) ? sanitize_key( (string) ( $finding['category'] ?? '' ) ) : '',
				'finding_id' => is_array( $finding ) ? sanitize_key( (string) ( $finding['id'] ?? '' ) ) : '',
			);
		} catch ( \Throwable $e ) {
			self::log_bulk_error( 'Bulk pending diagnostic execution failed: ' . $qualified, $e );

			return array( 'recordable' => false );
		}
	}

	/**
	 * Log bulk runner errors without assuming specific logger methods exist.
	 *
	 * @param string     $message Log message.
	 * @param \Throwable $error   Throwable error object.
	 * @return void
	 */
	protected static function log_bulk_error( string $message, \Throwable $error ): void {
		if ( class_exists( '\\WPShadow\\Core\\Error_Handler' ) && method_exists( '\\WPShadow\\Core\\Error_Handler', 'log_error' ) ) {
			\WPShadow\Core\Error_Handler::log_error( $message, $error );
			return;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Fallback logging in error path.
		error_log( $message . ' :: ' . $error->getMessage() );
	}

	/**
	 * Build per-user queue transient key.
	 *
	 * @return string
	 */
	protected static function get_queue_key(): string {
		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			$user_id = 0;
		}

		return 'wpshadow_bulk_pending_queue_' . $user_id;
	}

	/**
	 * Normalize class name to fully-qualified diagnostics namespace.
	 *
	 * @param string $class_name Diagnostic class name.
	 * @return string
	 */
	protected static function normalize_class_name( string $class_name ): string {
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
	 * Build scheduler key from class name.
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @return string
	 */
	protected static function get_run_key( string $class_name ): string {
		$short_name = str_replace( 'WPShadow\\Diagnostics\\', '', $class_name );
		$short_name = strtolower( str_replace( '_', '-', $short_name ) );
		return sanitize_key( $short_name );
	}
}
