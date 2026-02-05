<?php
/**
 * Performance Alerts Configuration Diagnostic
 *
 * Checks if performance threshold alerts are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Alerts Configuration Diagnostic Class
 *
 * Verifies alerts trigger when performance degrades.
 * Like getting notified when your store is too crowded or slow.
 *
 * @since 1.6035.1630
 */
class Diagnostic_Performance_Alerts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-alerts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Alerts Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance threshold alerts are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'real-user-monitoring';

	/**
	 * Run the performance alerts diagnostic check.
	 *
	 * @since  1.6035.1630
	 * @return array|null Finding array if alerts not configured, null otherwise.
	 */
	public static function check() {
		// Check if performance monitoring with alerts exists.
		$monitoring_services = array(
			'New Relic'        => function_exists( 'newrelic_get_browser_timing_header' ),
			'Query Monitor'    => class_exists( 'QueryMonitor' ),
			'Google Site Kit'  => defined( 'GOOGLESITEKIT_VERSION' ),
			'Cloudflare'       => isset( $_SERVER['HTTP_CF_RAY'] ),
		);

		$active_monitoring = array();
		foreach ( $monitoring_services as $name => $detected ) {
			if ( $detected ) {
				$active_monitoring[] = $name;
			}
		}

		// Check if custom performance alerts are configured.
		$custom_alerts = array(
			'slow_page'     => get_option( 'wpshadow_alert_slow_pages', false ),
			'high_cpu'      => get_option( 'wpshadow_alert_high_cpu', false ),
			'memory_limit'  => get_option( 'wpshadow_alert_memory', false ),
			'db_slow'       => get_option( 'wpshadow_alert_slow_queries', false ),
		);

		$configured_alerts = array_filter( $custom_alerts );

		if ( empty( $active_monitoring ) && empty( $configured_alerts ) ) {
			return array(
				'id'           => self::$slug . '-not-configured',
				'title'        => __( 'Performance Alerts Not Configured', 'wpshadow' ),
				'description'  => __( 'You\'re not getting alerted when your site slows down (like not knowing your checkout line is too long until customers complain). Performance problems cost you money immediately—slow sites have higher bounce rates, lower conversions, and worse search rankings. Set up alerts for: page load time >3 seconds, database queries >1 second, memory usage >80%, CPU spikes. Services like New Relic, Cloudflare, or Google Search Console can send automatic alerts.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-alerts',
				'context'      => array(),
			);
		}

		// Check if alert thresholds are reasonable.
		$page_load_threshold = get_option( 'wpshadow_alert_page_load_threshold', 3000 );
		
		if ( $page_load_threshold > 5000 ) { // More than 5 seconds.
			return array(
				'id'           => self::$slug . '-threshold-too-high',
				'title'        => __( 'Performance Alert Threshold Too High', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: threshold time */
					__( 'Your performance alerts only trigger when pages take over %s to load (like only caring when checkout takes forever, not when it\'s just slow). By then, you\'ve already lost visitors and sales. Lower your threshold to 2-3 seconds—most users abandon sites that take longer than 3 seconds. Fast sites make money; slow sites lose it.', 'wpshadow' ),
					number_format( $page_load_threshold / 1000, 1 ) . 's'
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-thresholds',
				'context'      => array(
					'page_load_threshold' => $page_load_threshold,
				),
			);
		}

		// Check if alerts have been triggered recently (maybe too sensitive).
		$recent_alerts = get_transient( 'wpshadow_recent_performance_alerts' );
		$alert_count = is_array( $recent_alerts ) ? count( $recent_alerts ) : 0;

		if ( $alert_count > 50 ) { // More than 50 alerts in recent period.
			return array(
				'id'           => self::$slug . '-too-many-alerts',
				'title'        => __( 'Too Many Performance Alerts', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: number of alerts */
					__( 'You received %d performance alerts recently (like a car alarm that goes off constantly—people stop paying attention). When alerts fire too often, you start ignoring them, defeating the purpose. Review your thresholds—they may be too sensitive. Adjust to only alert on genuine problems: sustained slow performance, not brief spikes. Focus on alerts that require action.', 'wpshadow' ),
					$alert_count
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/alert-fatigue',
				'context'      => array(
					'alert_count' => $alert_count,
				),
			);
		}

		return null; // Performance alerts are properly configured.
	}
}
