<?php
/**
 * Report Alert Manager
 *
 * Handles threshold-based alerts for report metrics.
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report_Alert_Manager Class
 *
 * Manages alerts and thresholds for report metrics.
 *
 * @since 0.6093.1200
 */
class Report_Alert_Manager {

	/**
	 * Set an alert threshold
	 *
	 * @since 0.6093.1200
	 * @param  string $metric     Metric name.
	 * @param  string $operator   Comparison operator (gt, lt, gte, lte, eq).
	 * @param  mixed  $threshold  Threshold value.
	 * @param  array  $options    Alert options.
	 * @return bool Success.
	 */
	public static function set_alert( $metric, $operator, $threshold, $options = array() ) {
		$alerts = get_option( 'wpshadow_report_alerts', array() );

		$alert_id = 'alert_' . time() . '_' . wp_rand( 1000, 9999 );

		$alerts[ $alert_id ] = array(
			'metric'     => sanitize_key( $metric ),
			'operator'   => sanitize_key( $operator ),
			'threshold'  => $threshold,
			'enabled'    => true,
			'recipients' => isset( $options['recipients'] ) ? array_map( 'sanitize_email', $options['recipients'] ) : array(),
			'severity'   => isset( $options['severity'] ) ? sanitize_key( $options['severity'] ) : 'warning',
			'created'    => time(),
			'last_triggered' => 0,
		);

		update_option( 'wpshadow_report_alerts', $alerts );

		return true;
	}

	/**
	 * Check if metric triggers alert
	 *
	 * @since 0.6093.1200
	 * @param  string $metric Metric name.
	 * @param  mixed  $value  Current value.
	 * @return array Triggered alerts.
	 */
	public static function check_alerts( $metric, $value ) {
		$alerts = get_option( 'wpshadow_report_alerts', array() );
		$triggered = array();

		foreach ( $alerts as $alert_id => $alert ) {
			if ( ! $alert['enabled'] || $alert['metric'] !== $metric ) {
				continue;
			}

			if ( self::evaluate_condition( $value, $alert['operator'], $alert['threshold'] ) ) {
				$triggered[] = $alert_id;
				self::trigger_alert( $alert_id, $alert, $value );
			}
		}

		return $triggered;
	}

	/**
	 * Evaluate condition
	 *
	 * @since 0.6093.1200
	 * @param  mixed  $value     Current value.
	 * @param  string $operator  Operator.
	 * @param  mixed  $threshold Threshold.
	 * @return bool Condition met.
	 */
	private static function evaluate_condition( $value, $operator, $threshold ) {
		switch ( $operator ) {
			case 'gt':
				return $value > $threshold;
			case 'lt':
				return $value < $threshold;
			case 'gte':
				return $value >= $threshold;
			case 'lte':
				return $value <= $threshold;
			case 'eq':
				return $value == $threshold;
			default:
				return false;
		}
	}

	/**
	 * Trigger an alert
	 *
	 * @since 0.6093.1200
	 * @param  string $alert_id Alert ID.
	 * @param  array  $alert    Alert data.
	 * @param  mixed  $value    Current value.
	 * @return void
	 */
	private static function trigger_alert( $alert_id, $alert, $value ) {
		// Update last triggered time
		$alerts = get_option( 'wpshadow_report_alerts', array() );
		$alerts[ $alert_id ]['last_triggered'] = time();
		update_option( 'wpshadow_report_alerts', $alerts );

		// Log alert
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'alert_triggered',
				array(
					'alert_id' => $alert_id,
					'metric'   => $alert['metric'],
					'value'    => $value,
					'threshold' => $alert['threshold'],
					'severity' => $alert['severity'],
				)
			);
		}

		/**
		 * Fires when an alert is triggered.
		 *
		 * @since 0.6093.1200
		 *
		 * @param string $alert_id Alert ID.
		 * @param array  $alert Alert data.
		 * @param mixed  $value Current value.
		 */
		do_action( 'wpshadow_alert_triggered', $alert_id, $alert, $value );
	}

	/**
	 * Get all alerts
	 *
	 * @since 0.6093.1200
	 * @return array Alerts.
	 */
	public static function get_alerts() {
		return get_option( 'wpshadow_report_alerts', array() );
	}

	/**
	 * Delete an alert
	 *
	 * @since 0.6093.1200
	 * @param  string $alert_id Alert ID.
	 * @return bool Success.
	 */
	public static function delete_alert( $alert_id ) {
		$alerts = get_option( 'wpshadow_report_alerts', array() );

		if ( isset( $alerts[ $alert_id ] ) ) {
			unset( $alerts[ $alert_id ] );
			update_option( 'wpshadow_report_alerts', $alerts );
			return true;
		}

		return false;
	}
}
