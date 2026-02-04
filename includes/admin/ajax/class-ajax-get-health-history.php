<?php
/**
 * Health History AJAX Handler
 *
 * Handles AJAX requests for health history data.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.602.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Analytics\Health_History;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Health History AJAX Handler
 *
 * @since 1.602.0200
 */
class AJAX_Get_Health_History extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.602.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		// Verify nonce and capability.
		self::verify_request( 'wpshadow_get_health_history', 'manage_options' );

		// Get date range parameter.
		$date_range = self::get_post_param( 'date_range', 'int', 30 );

		// Validate date range.
		$allowed_ranges = array( 7, 30, 60, 90 );
		if ( ! in_array( $date_range, $allowed_ranges, true ) ) {
			$date_range = 30;
		}

		// Check cache first.
		$cache_key = "wpshadow_health_history_{$date_range}";
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			self::send_success( $cached_data );
			return;
		}

		// Get history data.
		$history = Health_History::get_history( $date_range );
		$summary = Health_History::get_summary( $date_range );

		// Prepare chart data.
		$chart_data = array(
			'labels'     => array(),
			'datasets'   => array(
				'overall_health' => array(),
				'security'       => array(),
				'performance'    => array(),
				'quality'        => array(),
			),
			'issues'     => array(
				'critical' => array(),
				'high'     => array(),
				'medium'   => array(),
				'low'      => array(),
			),
		);

		foreach ( $history as $date => $snapshot ) {
			$chart_data['labels'][] = $date;
			$chart_data['datasets']['overall_health'][] = $snapshot['overall_health'];
			$chart_data['datasets']['security'][] = $snapshot['security'];
			$chart_data['datasets']['performance'][] = $snapshot['performance'];
			$chart_data['datasets']['quality'][] = $snapshot['quality'];
			
			$chart_data['issues']['critical'][] = $snapshot['critical_count'];
			$chart_data['issues']['high'][] = $snapshot['high_count'];
			$chart_data['issues']['medium'][] = $snapshot['medium_count'];
			$chart_data['issues']['low'][] = $snapshot['low_count'];
		}

		$response = array(
			'chart_data' => $chart_data,
			'summary'    => $summary,
			'date_range' => $date_range,
		);

		// Cache for 1 hour.
		set_transient( $cache_key, $response, HOUR_IN_SECONDS );

		self::send_success( $response );
	}
}

// Register AJAX handler.
add_action( 'wp_ajax_wpshadow_get_health_history', array( 'WPShadow\Admin\AJAX_Get_Health_History', 'handle' ) );
