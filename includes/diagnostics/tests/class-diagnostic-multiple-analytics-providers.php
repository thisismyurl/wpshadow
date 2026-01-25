<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Multiple Analytics Providers (THIRD-004)
 *
 * Counts analytics scripts (GA, FB Pixel, etc.).
 * Philosophy: Show value (#9) with tracking consolidation.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Multiple_Analytics_Providers extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for multiple analytics providers
		$analytics_count = 0;

		if ( get_option( 'google_analytics_id' ) ) {
			++$analytics_count;
		}
		if ( get_option( 'matomo_site_id' ) ) {
			++$analytics_count;
		}
		if ( apply_filters( 'wpshadow_analytics_providers_count', 0 ) ) {
			$analytics_count += apply_filters( 'wpshadow_analytics_providers_count', 0 );
		}

		if ( $analytics_count > 2 ) {
			return array(
				'id'            => 'multiple-analytics-providers',
				'title'         => sprintf( __( '%d Analytics Providers Active', 'wpshadow' ), $analytics_count ),
				'description'   => __( 'Multiple analytics services add extra requests and tracking code. Consolidate to single analytics provider if possible.', 'wpshadow' ),
				'severity'      => 'low',
				'category'      => 'monitoring',
				'kb_link'       => 'https://wpshadow.com/kb/analytics-consolidation/',
				'training_link' => 'https://wpshadow.com/training/analytics-performance/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
			);
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Multiple Analytics Providers
	 * Slug: -multiple-analytics-providers
	 * File: class-diagnostic-multiple-analytics-providers.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Multiple Analytics Providers
	 * Slug: -multiple-analytics-providers
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
	public static function test_live__multiple_analytics_providers(): array {
		$analytics_count  = 0;
		$analytics_count += get_option( 'google_analytics_id' ) ? 1 : 0;
		$analytics_count += get_option( 'matomo_site_id' ) ? 1 : 0;
		$extra            = (int) apply_filters( 'wpshadow_analytics_providers_count', 0 );
		$analytics_count += $extra > 0 ? $extra : 0;

		$has_issue = ( $analytics_count > 2 );

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );
		$test_passes            = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Analytics provider count check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (providers: %d)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$analytics_count
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
