<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Traffic_Anomaly_Detection extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'monitor-traffic-anomaly',
			'title'         => __( 'Traffic Volume Anomaly Detection', 'wpshadow' ),
			'description'   => __( 'Detects abnormal traffic spikes or drops. Sudden drop = server issues/outage. Spike = DDoS or viral content.', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/traffic-health/',
			'training_link' => 'https://wpshadow.com/training/analytics-monitoring/',
			'auto_fixable'  => false,
			'threat_level'  => 7,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Traffic Anomaly Detection
	 * Slug: -monitor-traffic-anomaly-detection
	 * File: class-diagnostic-monitor-traffic-anomaly-detection.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Traffic Anomaly Detection
	 * Slug: -monitor-traffic-anomaly-detection
	 *
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__monitor_traffic_anomaly_detection(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Traffic patterns are normal - no anomalies detected',
			);
		}
		$message = $result['description'] ?? 'Traffic anomaly detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
