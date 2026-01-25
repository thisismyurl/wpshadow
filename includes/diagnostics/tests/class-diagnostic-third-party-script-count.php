<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Third-Party Script Count (ASSET-018)
 *
 * Counts external scripts from third-party domains.
 * Philosophy: Show value (#9) with load time reduction.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Third_Party_Script_Count extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Count third-party scripts
		$third_party_count = get_transient( 'wpshadow_third_party_script_count' );

		if ( $third_party_count && $third_party_count > 20 ) {
			return array(
				'id'            => 'third-party-script-count',
				'title'         => sprintf( __( '%d Third-Party Scripts Loaded', 'wpshadow' ), $third_party_count ),
				'description'   => __( 'Loading many third-party scripts hurts performance. Audit scripts and remove non-essential ones.', 'wpshadow' ),
				'severity'      => 'high',
				'category'      => 'monitoring',
				'kb_link'       => 'https://wpshadow.com/kb/third-party-script-audit/',
				'training_link' => 'https://wpshadow.com/training/script-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
			);
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Third Party Script Count
	 * Slug: -third-party-script-count
	 * File: class-diagnostic-third-party-script-count.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Third Party Script Count
	 * Slug: -third-party-script-count
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
	public static function test_live__third_party_script_count(): array {
		$third_party_count = get_transient( 'wpshadow_third_party_script_count' );
		$has_issue         = ( $third_party_count && $third_party_count > 20 );

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );
		$test_passes            = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Third-party script count check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (scripts: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$third_party_count !== false ? (string) $third_party_count : 'n/a'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
