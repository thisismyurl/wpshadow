<?php
/**
 * Usage Tracker - Track Feature Usage and Calculate ROI
 *
 * Tracks utility usage and calculates time/money saved.
 * Implements #9 Everything Has a KPI.
 *
 * @package    WPShadow
 * @subpackage Analytics
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Analytics;

use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Usage Tracker Class
 *
 * Tracks feature usage and calculates impact metrics.
 *
 * @since 1.2601.2200
 */
class Usage_Tracker {

	/**
	 * Time savings per utility (in minutes).
	 *
	 * @var array
	 */
	private static $time_savings = array(
		'site_cloner'             => 45,  // 45 minutes per clone
		'code_snippets'           => 20,  // 20 minutes per snippet
		'plugin_conflict'         => 135, // 2h 15min per conflict resolution
		'bulk_find_replace'       => 60,  // 1 hour per operation
		'regenerate_thumbnails'   => 50,  // 50 minutes per batch
		'database_optimization'   => 15,  // 15 minutes per optimization
		'treatment_applied'       => 30,  // 30 minutes per manual fix
		'diagnostic_run'          => 5,   // 5 minutes per manual check
		'workflow_recipe_execute' => 45,  // 45 minutes per workflow
	);

	/**
	 * Initialize the usage tracker.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function init() {
		// Hook into Activity_Logger events
		add_action( 'wpshadow_activity_logged', array( __CLASS__, 'track_activity' ), 10, 1 );

		// AJAX endpoint for fetching stats
		add_action( 'wp_ajax_wpshadow_get_usage_stats', array( __CLASS__, 'ajax_get_stats' ) );
	}

	/**
	 * Track activity and calculate savings.
	 *
	 * @since  1.2601.2200
	 * @param  array $activity Activity array with keys: action, details, metadata, etc.
	 * @return void
	 */
	public static function track_activity( $activity ) {
		// Validate activity parameter
		if ( ! is_array( $activity ) || ! isset( $activity['action'] ) ) {
			return;
		}

		$action = $activity['action'];

		// Map activity actions to utility types
		$utility_map = array(
			'site_clone_started'          => 'site_cloner',
			'snippet_saved'               => 'code_snippets',
			'plugin_conflict_detected'    => 'plugin_conflict',
			'find_replace_executed'       => 'bulk_find_replace',
			'thumbnails_regenerated'      => 'regenerate_thumbnails',
			'database_optimized'          => 'database_optimization',
			'treatment_applied'           => 'treatment_applied',
			'diagnostic_completed'        => 'diagnostic_run',
			'workflow_recipe_completed'   => 'workflow_recipe_execute',
		);

		// Check if this activity maps to a tracked utility
		if ( ! isset( $utility_map[ $action ] ) ) {
			return;
		}

		$utility = $utility_map[ $action ];

		// Get current usage stats
		$stats = self::get_stats();

		// Increment usage count
		if ( ! isset( $stats['usage_counts'][ $utility ] ) ) {
			$stats['usage_counts'][ $utility ] = 0;
		}
		$stats['usage_counts'][ $utility ]++;

		// Add time saved
		$time_saved = self::$time_savings[ $utility ] ?? 0;
		if ( ! isset( $stats['time_saved'][ $utility ] ) ) {
			$stats['time_saved'][ $utility ] = 0;
		}
		$stats['time_saved'][ $utility ] += $time_saved;

		// Update total time saved
		if ( ! isset( $stats['total_time_saved'] ) ) {
			$stats['total_time_saved'] = 0;
		}
		$stats['total_time_saved'] += $time_saved;

		// Update last used timestamp
		$stats['last_used'][ $utility ] = current_time( 'timestamp' );

		// Update global stats
		update_option( 'wpshadow_usage_stats', $stats );
	}

	/**
	 * Get usage statistics.
	 *
	 * @since  1.2601.2200
	 * @param  int $period Optional. Time period in days. Default 0 (all time).
	 * @return array Usage statistics.
	 */
	public static function get_stats( $period = 0 ) {
		$stats = get_option(
			'wpshadow_usage_stats',
			array(
				'usage_counts'     => array(),
				'time_saved'       => array(),
				'total_time_saved' => 0,
				'last_used'        => array(),
				'first_install'    => current_time( 'timestamp' ),
			)
		);

		// If period specified, filter by date
		if ( $period > 0 ) {
			$cutoff_time = current_time( 'timestamp' ) - ( $period * DAY_IN_SECONDS );
			$stats       = self::filter_stats_by_period( $stats, $cutoff_time );
		}

		return $stats;
	}

	/**
	 * Filter statistics by time period.
	 *
	 * @since  1.2601.2200
	 * @param  array $stats       Full statistics array.
	 * @param  int   $cutoff_time Unix timestamp cutoff.
	 * @return array Filtered statistics.
	 */
	private static function filter_stats_by_period( $stats, $cutoff_time ) {
		// Get activity logs for the period
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpshadow_activity';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$activities = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT action, metadata FROM {$table_name} WHERE created_at >= %s",
				gmdate( 'Y-m-d H:i:s', $cutoff_time )
			)
		);

		// Recalculate stats for this period
		$period_stats = array(
			'usage_counts'     => array(),
			'time_saved'       => array(),
			'total_time_saved' => 0,
			'last_used'        => array(),
		);

		foreach ( $activities as $activity ) {
			$action = $activity->action;

			// Map to utility
			$utility_map = array(
				'site_clone_started'        => 'site_cloner',
				'snippet_saved'             => 'code_snippets',
				'plugin_conflict_detected'  => 'plugin_conflict',
				'find_replace_executed'     => 'bulk_find_replace',
				'thumbnails_regenerated'    => 'regenerate_thumbnails',
				'database_optimized'        => 'database_optimization',
				'treatment_applied'         => 'treatment_applied',
				'diagnostic_completed'      => 'diagnostic_run',
				'workflow_recipe_completed' => 'workflow_recipe_execute',
			);

			if ( ! isset( $utility_map[ $action ] ) ) {
				continue;
			}

			$utility = $utility_map[ $action ];

			// Increment counts
			if ( ! isset( $period_stats['usage_counts'][ $utility ] ) ) {
				$period_stats['usage_counts'][ $utility ] = 0;
			}
			$period_stats['usage_counts'][ $utility ]++;

			// Add time saved
			$time_saved = self::$time_savings[ $utility ] ?? 0;
			if ( ! isset( $period_stats['time_saved'][ $utility ] ) ) {
				$period_stats['time_saved'][ $utility ] = 0;
			}
			$period_stats['time_saved'][ $utility ] += $time_saved;
			$period_stats['total_time_saved']       += $time_saved;
		}

		return $period_stats;
	}

	/**
	 * Get most used utility.
	 *
	 * @since  1.2601.2200
	 * @param  int $period Optional. Time period in days. Default 0 (all time).
	 * @return array {
	 *     Most used utility data.
	 *
	 *     @type string $utility Utility slug.
	 *     @type int    $count   Usage count.
	 *     @type int    $saved   Time saved in minutes.
	 * }
	 */
	public static function get_most_used( $period = 0 ) {
		$stats = self::get_stats( $period );

		if ( empty( $stats['usage_counts'] ) ) {
			return array(
				'utility' => '',
				'count'   => 0,
				'saved'   => 0,
			);
		}

		// Find utility with highest usage count
		$utility = array_keys( $stats['usage_counts'], max( $stats['usage_counts'] ) )[0];

		return array(
			'utility' => $utility,
			'count'   => $stats['usage_counts'][ $utility ],
			'saved'   => $stats['time_saved'][ $utility ] ?? 0,
		);
	}

	/**
	 * Calculate money saved based on hourly rate.
	 *
	 * @since  1.2601.2200
	 * @param  int $time_saved_minutes Time saved in minutes.
	 * @param  int $hourly_rate        Hourly rate in dollars. Default 100.
	 * @return float Money saved in dollars.
	 */
	public static function calculate_money_saved( $time_saved_minutes, $hourly_rate = 100 ) {
		$hours_saved = $time_saved_minutes / 60;
		return round( $hours_saved * $hourly_rate, 2 );
	}

	/**
	 * Get utility label.
	 *
	 * @since  1.2601.2200
	 * @param  string $utility Utility slug.
	 * @return string Human-readable utility label.
	 */
	public static function get_utility_label( $utility ) {
		$labels = array(
			'site_cloner'             => __( 'Site Cloner', 'wpshadow' ),
			'code_snippets'           => __( 'Code Snippets', 'wpshadow' ),
			'plugin_conflict'         => __( 'Plugin Conflict Detector', 'wpshadow' ),
			'bulk_find_replace'       => __( 'Bulk Find & Replace', 'wpshadow' ),
			'regenerate_thumbnails'   => __( 'Regenerate Thumbnails', 'wpshadow' ),
			'database_optimization'   => __( 'Database Optimization', 'wpshadow' ),
			'treatment_applied'       => __( 'Auto-Fix Treatments', 'wpshadow' ),
			'diagnostic_run'          => __( 'Health Diagnostics', 'wpshadow' ),
			'workflow_recipe_execute' => __( 'Workflow Recipes', 'wpshadow' ),
		);

		return $labels[ $utility ] ?? $utility;
	}

	/**
	 * AJAX: Get usage statistics.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function ajax_get_stats() {
		check_ajax_referer( 'wpshadow_usage_stats', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$period = isset( $_POST['period'] ) ? absint( $_POST['period'] ) : 0;

		$stats     = self::get_stats( $period );
		$most_used = self::get_most_used( $period );

		wp_send_json_success(
			array(
				'stats'     => $stats,
				'most_used' => $most_used,
			)
		);
	}
}
