<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Third_Party_Service_Status extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'monitor-service-status',
			'title'         => __( 'Third-Party Service Status Monitoring', 'wpshadow' ),
			'description'   => __( 'Tracks status of external services (CDN, email, payment, API). Degradation = user-facing impact.', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/service-dependencies/',
			'training_link' => 'https://wpshadow.com/training/dependency-management/',
			'auto_fixable'  => false,
			'threat_level'  => 8,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Third Party Service Status
	 * Slug: -monitor-third-party-service-status
	 * File: class-diagnostic-monitor-third-party-service-status.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Third Party Service Status
	 * Slug: -monitor-third-party-service-status
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
	public static function test_live__monitor_third_party_service_status(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'All third-party services are operational',
			);
		}
		$message = $result['description'] ?? 'Third-party service issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
