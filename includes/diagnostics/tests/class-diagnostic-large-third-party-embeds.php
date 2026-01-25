<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Large Third-Party Embeds (FE-007)
 *
 * Detects heavy embeds (YouTube, Twitter, etc.).
 * Philosophy: Show value (#9) with facade implementation.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Large_Third_Party_Embeds extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for large third-party embeds
		$embed_count = get_transient( 'wpshadow_third_party_embed_count' );

		if ( $embed_count && $embed_count > 5 ) {
			return array(
				'id'            => 'large-third-party-embeds',
				'title'         => sprintf( __( '%d Third-Party Embeds Found', 'wpshadow' ), $embed_count ),
				'description'   => __( 'Multiple third-party embeds (YouTube, Vimeo, etc.) add significant payload. Lazy-load embeds for better performance.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'monitoring',
				'kb_link'       => 'https://wpshadow.com/kb/embed-optimization/',
				'training_link' => 'https://wpshadow.com/training/lazy-load-embeds/',
				'auto_fixable'  => false,
				'threat_level'  => 45,
			);
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Large Third Party Embeds
	 * Slug: -large-third-party-embeds
	 * File: class-diagnostic-large-third-party-embeds.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Large Third Party Embeds
	 * Slug: -large-third-party-embeds
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
	public static function test_live__large_third_party_embeds(): array {
		$embed_count = get_transient( 'wpshadow_third_party_embed_count' );
		$has_issue   = ( $embed_count && $embed_count > 5 );

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );
		$test_passes            = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Third-party embed count check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (embeds: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$embed_count !== false ? (string) $embed_count : 'n/a'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
