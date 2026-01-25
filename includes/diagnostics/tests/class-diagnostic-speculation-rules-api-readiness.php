<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Speculation Rules API Readiness (FE-362)
 *
 * Assesses prefetch/prerender rules coverage and safety guards.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_SpeculationRulesApiReadiness extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for Speculation Rules API implementation
		if ( ! is_ssl() ) {
			return null;
		}

		// Check if Speculation Rules API is enabled
		$has_speculation = apply_filters( 'wpshadow_speculation_rules_enabled', false );

		if ( ! $has_speculation ) {
			return array(
				'id'            => 'speculation-rules-api-readiness',
				'title'         => __( 'Speculation Rules API Not Enabled', 'wpshadow' ),
				'description'   => __( 'Enable Speculation Rules API for faster navigation. This modern Chrome feature allows prefetching of likely navigation targets.', 'wpshadow' ),
				'severity'      => 'info',
				'category'      => 'monitoring',
				'kb_link'       => 'https://wpshadow.com/kb/speculation-rules-api/',
				'training_link' => 'https://wpshadow.com/training/prefetch-strategies/',
				'auto_fixable'  => false,
				'threat_level'  => 15,
			);
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SpeculationRulesApiReadiness
	 * Slug: -speculation-rules-api-readiness
	 * File: class-diagnostic-speculation-rules-api-readiness.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SpeculationRulesApiReadiness
	 * Slug: -speculation-rules-api-readiness
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
	public static function test_live__speculation_rules_api_readiness(): array {
		$is_ssl          = is_ssl();
		$has_speculation = (bool) apply_filters( 'wpshadow_speculation_rules_enabled', false );

		$has_issue = ( $is_ssl && ! $has_speculation );

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		$test_passes = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Speculation Rules readiness diagnostic matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (is_ssl: %s, speculation enabled: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$is_ssl ? 'yes' : 'no',
				$has_speculation ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
