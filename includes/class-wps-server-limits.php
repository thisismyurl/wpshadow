<?php
/**
 * WPS Server Limits Manager
 *
 * Monitors server resource usage and provides methods to gracefully handle
 * resource constraints by batching or skipping operations.
 *
 * @package wp_support_SUPPORT
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPS_Server_Limits
 *
 * Manages server resource limits and provides graceful degradation.
 */
class WPS_Server_Limits {

	/**
	 * Memory threshold percentage for warnings (80%).
	 */
	private const MEMORY_WARNING_THRESHOLD = 0.8;

	/**
	 * Memory threshold percentage for critical (90%).
	 */
	private const MEMORY_CRITICAL_THRESHOLD = 0.9;

	/**
	 * Execution time threshold percentage for warnings (75%).
	 */
	private const TIME_WARNING_THRESHOLD = 0.75;

	/**
	 * Execution time threshold percentage for critical (85%).
	 */
	private const TIME_CRITICAL_THRESHOLD = 0.85;

	/**
	 * Default batch size for constrained environments.
	 */
	private const DEFAULT_BATCH_SIZE = 10;

	/**
	 * Large batch size for normal environments.
	 */
	private const LARGE_BATCH_SIZE = 50;

	/**
	 * Small batch size for very constrained environments.
	 */
	private const SMALL_BATCH_SIZE = 5;

	/**
	 * Initialize server limits manager.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Hook into long-running operations to check limits.
		add_action( 'wps_before_batch_operation', array( __CLASS__, 'check_limits_before_operation' ) );
	}

	/**
	 * Get current memory usage status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_memory_status(): array {
		$memory_limit       = ini_get( 'memory_limit' );
		$memory_limit_bytes = WPS_Environment_Checker::get_memory_limit_status()['current_bytes'];
		$current_usage      = memory_get_usage( true );
		$peak_usage         = memory_get_peak_usage( true );

		$usage_percentage = $memory_limit_bytes > 0 ? ( $current_usage / $memory_limit_bytes ) : 0;
		$peak_percentage  = $memory_limit_bytes > 0 ? ( $peak_usage / $memory_limit_bytes ) : 0;

		$level = 'good';
		if ( $usage_percentage >= self::MEMORY_CRITICAL_THRESHOLD ) {
			$level = 'critical';
		} elseif ( $usage_percentage >= self::MEMORY_WARNING_THRESHOLD ) {
			$level = 'warning';
		}

		return array(
			'limit'            => $memory_limit,
			'limit_bytes'      => $memory_limit_bytes,
			'current_usage'    => $current_usage,
			'peak_usage'       => $peak_usage,
			'available'        => max( 0, $memory_limit_bytes - $current_usage ),
			'usage_percentage' => round( $usage_percentage * 100, 2 ),
			'peak_percentage'  => round( $peak_percentage * 100, 2 ),
			'level'            => $level,
			'should_batch'     => $usage_percentage >= self::MEMORY_WARNING_THRESHOLD,
			'should_stop'      => $usage_percentage >= self::MEMORY_CRITICAL_THRESHOLD,
		);
	}

	/**
	 * Get current execution time status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_execution_time_status(): array {
		$max_execution = (int) ini_get( 'max_execution_time' );
		$start_time    = isset( $_SERVER['REQUEST_TIME_FLOAT'] ) && is_numeric( $_SERVER['REQUEST_TIME_FLOAT'] )
			? (float) $_SERVER['REQUEST_TIME_FLOAT']
			: microtime( true );
		$elapsed       = microtime( true ) - $start_time;

		// If max_execution_time is 0, it's unlimited.
		if ( 0 === $max_execution ) {
			return array(
				'max_execution_time' => 0,
				'elapsed'            => $elapsed,
				'remaining'          => PHP_INT_MAX,
				'usage_percentage'   => 0,
				'level'              => 'good',
				'should_batch'       => false,
				'should_stop'        => false,
			);
		}

		$remaining        = max( 0, $max_execution - $elapsed );
		$usage_percentage = $elapsed / $max_execution;

		$level = 'good';
		if ( $usage_percentage >= self::TIME_CRITICAL_THRESHOLD ) {
			$level = 'critical';
		} elseif ( $usage_percentage >= self::TIME_WARNING_THRESHOLD ) {
			$level = 'warning';
		}

		return array(
			'max_execution_time' => $max_execution,
			'elapsed'            => round( $elapsed, 2 ),
			'remaining'          => round( $remaining, 2 ),
			'usage_percentage'   => round( $usage_percentage * 100, 2 ),
			'level'              => $level,
			'should_batch'       => $usage_percentage >= self::TIME_WARNING_THRESHOLD,
			'should_stop'        => $usage_percentage >= self::TIME_CRITICAL_THRESHOLD,
		);
	}

	/**
	 * Get combined resource status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_resource_status(): array {
		$memory = self::get_memory_status();
		$time   = self::get_execution_time_status();

		// Determine overall level (worst of both).
		$levels             = array( 'good', 'warning', 'critical' );
		$memory_level_index = array_search( $memory['level'], $levels, true );
		$time_level_index   = array_search( $time['level'], $levels, true );

		// If either search fails, default to 0 (good).
		if ( false === $memory_level_index ) {
			$memory_level_index = 0;
		}
		if ( false === $time_level_index ) {
			$time_level_index = 0;
		}

		$overall_level = $levels[ max( $memory_level_index, $time_level_index ) ];

		return array(
			'memory'       => $memory,
			'time'         => $time,
			'level'        => $overall_level,
			'should_batch' => $memory['should_batch'] || $time['should_batch'],
			'should_stop'  => $memory['should_stop'] || $time['should_stop'],
			'checked_at'   => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if operation should be batched based on current limits.
	 *
	 * @return bool
	 */
	public static function should_batch_operation(): bool {
		$status = self::get_resource_status();
		return $status['should_batch'];
	}

	/**
	 * Check if operation should be stopped based on current limits.
	 *
	 * @return bool
	 */
	public static function should_stop_operation(): bool {
		$status = self::get_resource_status();
		return $status['should_stop'];
	}

	/**
	 * Get recommended batch size based on current resource status.
	 *
	 * @return int
	 */
	public static function get_batch_size(): int {
		$status = self::get_resource_status();

		if ( 'critical' === $status['level'] ) {
			return self::SMALL_BATCH_SIZE;
		}

		if ( 'warning' === $status['level'] ) {
			return self::DEFAULT_BATCH_SIZE;
		}

		// Check if environment has constraints.
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Environment_Checker' )
			&& WPS_Environment_Checker::has_resource_constraints() ) {
			return self::DEFAULT_BATCH_SIZE;
		}

		return self::LARGE_BATCH_SIZE;
	}

	/**
	 * Calculate sleep time between batches (in seconds).
	 *
	 * @return int
	 */
	public static function get_batch_sleep_time(): int {
		$status = self::get_resource_status();

		if ( 'critical' === $status['level'] ) {
			return 2;
		}

		if ( 'warning' === $status['level'] ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Check limits before a batch operation.
	 *
	 * @param string $operation_name Operation identifier.
	 * @return void
	 */
	public static function check_limits_before_operation( string $operation_name ): void {
		$status = self::get_resource_status();

		if ( $status['should_stop'] ) {
			// Log critical resource status.
			if ( class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
				WPS_Activity_Logger::log(
					'resource_limit_critical',
					'system',
					array(
						'operation'         => $operation_name,
						'memory_percentage' => $status['memory']['usage_percentage'],
						'time_percentage'   => $status['time']['usage_percentage'],
					),
					'error'
				);
			}
		}
	}

	/**
	 * Check if heavy image operations should be allowed.
	 *
	 * @return bool
	 */
	public static function can_process_heavy_images(): bool {
		// Check environment compatibility first.
		if ( WPS_Environment_Checker::should_disable_heavy_tasks() ) {
			return false;
		}

		// Check current resource status.
		$status = self::get_resource_status();
		return ! $status['should_stop'];
	}

	/**
	 * Check if vault operations should be allowed.
	 *
	 * @return bool
	 */
	public static function can_perform_vault_operations(): bool {
		// Check if vault requirements are met.
		$vault_status = WPS_Environment_Checker::get_module_requirements_status( 'vault' );
		if ( ! $vault_status['supported'] ) {
			return false;
		}

		// Check environment compatibility.
		if ( WPS_Environment_Checker::should_disable_heavy_tasks() ) {
			return false;
		}

		// Check current resource status.
		$status = self::get_resource_status();
		return ! $status['should_stop'];
	}

	/**
	 * Get maximum number of items to process in a single request.
	 *
	 * @param string $operation_type Operation type (e.g., 'image', 'vault', 'backup').
	 * @return int
	 */
	public static function get_max_items_per_request( string $operation_type = 'default' ): int {
		$base_limit = self::get_batch_size();

		// Adjust based on operation type.
		$multipliers = array(
			'image'   => 0.5,  // Images are heavier.
			'vault'   => 0.75, // Vault operations are moderately heavy.
			'backup'  => 0.5,  // Backup operations are heavy.
			'default' => 1.0,
		);

		$multiplier = $multipliers[ $operation_type ] ?? $multipliers['default'];

		/**
		 * Filter max items per request.
		 *
		 * @param int    $limit          Calculated limit.
		 * @param string $operation_type Operation type.
		 * @param int    $base_limit     Base limit before adjustment.
		 */
		return (int) apply_filters(
			'wps_max_items_per_request',
			max( 1, (int) ( $base_limit * $multiplier ) ),
			$operation_type,
			$base_limit
		);
	}

	/**
	 * Log resource usage for debugging.
	 *
	 * @param string $context Context identifier.
	 * @return void
	 */
	public static function log_resource_usage( string $context ): void {
		// Only log if diagnostic logging is enabled.
		if ( ! get_option( 'wps_diagnostic_logging_enabled', false ) ) {
			return;
		}

		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
			return;
		}

		$status = self::get_resource_status();

		WPS_Activity_Logger::log(
			'resource_usage',
			'system',
			array(
				'context'           => $context,
				'memory_usage'      => $status['memory']['current_usage'],
				'memory_percentage' => $status['memory']['usage_percentage'],
				'time_elapsed'      => $status['time']['elapsed'],
				'time_percentage'   => $status['time']['usage_percentage'],
				'level'             => $status['level'],
			),
			'info'
		);
	}

	/**
	 * Ensure minimum available memory for operation.
	 *
	 * @param int $required_bytes Required memory in bytes.
	 * @return bool True if enough memory is available.
	 */
	public static function ensure_memory_available( int $required_bytes ): bool {
		$status = self::get_memory_status();

		if ( $status['available'] < $required_bytes ) {
			return false;
		}

		return true;
	}

	/**
	 * Ensure minimum available time for operation.
	 *
	 * @param int $required_seconds Required time in seconds.
	 * @return bool True if enough time is available.
	 */
	public static function ensure_time_available( int $required_seconds ): bool {
		$status = self::get_execution_time_status();

		// If unlimited time, always return true.
		if ( 0 === $status['max_execution_time'] ) {
			return true;
		}

		if ( $status['remaining'] < $required_seconds ) {
			return false;
		}

		return true;
	}

	/**
	 * Get resource usage report for display.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_resource_report(): array {
		$status     = self::get_resource_status();
		$env_status = WPS_Environment_Checker::get_environment_status();

		return array(
			'environment'     => array(
				'php_version'    => $env_status['php_version']['current'],
				'wp_version'     => $env_status['wp_version']['current'],
				'memory_limit'   => $env_status['memory_limit']['current'],
				'execution_time' => $env_status['execution_time']['current'],
			),
			'current_usage'   => array(
				'memory'         => WPS_Environment_Checker::format_bytes( $status['memory']['current_usage'] ),
				'memory_percent' => $status['memory']['usage_percentage'] . '%',
				'time_elapsed'   => $status['time']['elapsed'] . 's',
				'time_percent'   => $status['time']['usage_percentage'] . '%',
			),
			'recommendations' => self::get_recommendations( $status ),
			'batch_config'    => array(
				'should_batch' => $status['should_batch'],
				'batch_size'   => self::get_batch_size(),
				'sleep_time'   => self::get_batch_sleep_time(),
			),
		);
	}

	/**
	 * Get recommendations based on current status.
	 *
	 * @param array<string, mixed> $status Resource status.
	 * @return array<string>
	 */
	private static function get_recommendations( array $status ): array {
		$recommendations = array();

		if ( 'critical' === $status['memory']['level'] ) {
			$recommendations[] = __( 'Memory usage is critical. Consider increasing PHP memory_limit.', 'plugin-wp-support-thisismyurl' );
		} elseif ( 'warning' === $status['memory']['level'] ) {
			$recommendations[] = __( 'Memory usage is high. Operations will be batched automatically.', 'plugin-wp-support-thisismyurl' );
		}

		if ( 'critical' === $status['time']['level'] ) {
			$recommendations[] = __( 'Execution time is critical. Consider increasing max_execution_time.', 'plugin-wp-support-thisismyurl' );
		} elseif ( 'warning' === $status['time']['level'] ) {
			$recommendations[] = __( 'Execution time is high. Long operations will be batched.', 'plugin-wp-support-thisismyurl' );
		}

		if ( empty( $recommendations ) ) {
			$recommendations[] = __( 'Resource usage is optimal.', 'plugin-wp-support-thisismyurl' );
		}

		return $recommendations;
	}
}
