<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Testimonial extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'monitor-testimonial_currency',
			'title'         => __( 'Testimonial Freshness', 'wpshadow' ),
			'description'   => __( 'Checks testimonial dates. Old testimonials = credibility loss.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/',
			'training_link' => 'https://wpshadow.com/training/',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Testimonial
	 * Slug: -monitor-testimonial-currency
	 * File: class-diagnostic-monitor-testimonial-currency.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Testimonial
	 * Slug: -monitor-testimonial-currency
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
	public static function test_live__monitor_testimonial_currency(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Testimonials are current and relevant',
			);
		}
		$message = $result['description'] ?? 'Outdated testimonials detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
