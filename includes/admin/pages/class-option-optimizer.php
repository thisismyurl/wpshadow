<?php
/**
 * Option Optimizer for WPShadow
 *
 * Optimizes WordPress options table usage:
 * - Batch option loading
 * - Autoload management
 * - Object caching integration
 *
 * Philosophy: Ridiculously Good (#7) - Optimize what matters
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option Optimizer class
 */
class Option_Optimizer {

	/**
	 * Cache for batch-loaded options
	 *
	 * @var array
	 */
	private static $option_cache = array();

	/**
	 * Initialize optimizer
	 */
	public static function init(): void {
		// Prime option cache on admin init
		add_action( 'admin_init', array( __CLASS__, 'prime_option_cache' ) );
		add_action( 'admin_init', array( __CLASS__, 'enforce_autoload_defaults' ), 20 );

		// Set autoload=false for large options
		add_action( 'update_option', array( __CLASS__, 'manage_autoload' ), 10, 3 );
	}

	/**
	 * Get options that should never autoload.
	 *
	 * @return array
	 */
	private static function get_no_autoload_options(): array {
		return array(
			'wpshadow_activity_log',        // Activity log can get large
			'wpshadow_events',              // Event logger
			'wpshadow_error_reports',       // Error reports
			'wpshadow_workflow_executions', // Execution log
			'wpshadow_workflows',           // Workflow definitions and metadata
			'wpshadow_kpi_tracking',        // KPI history and aggregates
			'wpshadow_dashboard_data',      // Dashboard data payload
			'wpshadow_diagnostic_test_states', // Diagnostic run-state map
			'wpshadow_kb_articles_v1',      // KB cache
		);
	}

	/**
	 * Prime cache with frequently used options in one query
	 */
	public static function prime_option_cache(): void {
		if ( ! \function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = \get_current_screen();
		if ( ! $screen || ! isset( $screen->id ) || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}

		// Batch load WPShadow options (1 query instead of multiple)
		$option_names = array(
			'wpshadow_workflows',
			'wpshadow_dismissed_findings',
			'wpshadow_excluded_findings',
			'wpshadow_manual_fixes',
			'wpshadow_scheduled_automated_fixes',
			'wpshadow_last_deep_scan',
		);

		// Use native get_option() which handles caching automatically
		// This is more WordPress-native and respects object cache
		foreach ( $option_names as $option_name ) {
			// get_option() automatically handles caching and unserialization
			$value = get_option( $option_name, false );
			if ( false !== $value ) {
				self::$option_cache[ $option_name ] = $value;
			}
		}
	}

	/**
	 * Get option from cache or database
	 *
	 * @param string $option_name Option name
	 * @param mixed  $default Default value
	 * @return mixed Option value
	 */
	public static function get_option( string $option_name, $default = false ) {
		// Check memory cache first
		if ( isset( self::$option_cache[ $option_name ] ) ) {
			return self::$option_cache[ $option_name ];
		}

		// Fall back to get_option (uses object cache)
		$value                              = get_option( $option_name, $default );
		self::$option_cache[ $option_name ] = $value;

		return $value;
	}

	/**
	 * Manage autoload flag to prevent option bloat
	 *
	 * @param string $option_name Option name
	 * @param mixed  $old_value Old value
	 * @param mixed  $new_value New value
	 */
	public static function manage_autoload( string $option_name, $old_value, $new_value ): void {
		// Skip non-WPShadow options
		if ( strpos( $option_name, 'wpshadow_' ) !== 0 ) {
			return;
		}

		$no_autoload = self::get_no_autoload_options();

		if ( in_array( $option_name, $no_autoload, true ) ) {
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->options} SET autoload = 'no' WHERE option_name = %s",
					$option_name
				)
			);
		}
	}

	/**
	 * Enforce autoload flags for large options on existing installs.
	 *
	 * Runs at most once every 24 hours in admin requests.
	 *
	 * @return void
	 */
	public static function enforce_autoload_defaults(): void {
		$last_run = (int) get_option( 'wpshadow_autoload_optimized_at', 0 );
		if ( $last_run > 0 && ( time() - $last_run ) < DAY_IN_SECONDS ) {
			return;
		}

		global $wpdb;

		if ( ! isset( $wpdb ) || ! ( $wpdb instanceof \wpdb ) ) {
			return;
		}

		foreach ( self::get_no_autoload_options() as $option_name ) {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->options} SET autoload = 'no' WHERE option_name = %s",
					$option_name
				)
			);
		}

		update_option( 'wpshadow_autoload_optimized_at', time() );
	}

	/**
	 * Get multiple options at once (batch operation)
	 *
	 * @param array $option_names Array of option names
	 * @return array Associative array of option_name => value
	 */
	public static function get_options( array $option_names ): array {
		$results  = array();
		$to_fetch = array();

		// Check cache first
		foreach ( $option_names as $name ) {
			if ( isset( self::$option_cache[ $name ] ) ) {
				$results[ $name ] = self::$option_cache[ $name ];
			} else {
				$to_fetch[] = $name;
			}
		}

		// Fetch remaining from database in one query
		if ( ! empty( $to_fetch ) ) {
			// Use native get_option() which handles caching, unserialization, and object cache
			foreach ( $to_fetch as $option_name ) {
				// get_option() automatically handles all caching layers
				$value = get_option( $option_name, false );
				if ( false !== $value ) {
					$results[ $option_name ]            = $value;
					self::$option_cache[ $option_name ] = $value;
				}
			}
		}

		return $results;
	}
}

// Initialize optimizer
Option_Optimizer::init();
