<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: External Comment Systems (THIRD-003)
 *
 * Detects Disqus, Facebook Comments, etc.
 * Philosophy: Educate (#5) about comment system alternatives.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_External_Comment_Systems extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for external comment systems
		$has_disqus   = function_exists( 'dsq_init' );
		$has_facebook = get_option( 'fb_app_id' );
		$has_custom   = apply_filters( 'wpshadow_external_comments_detected', false );

		if ( $has_disqus || $has_facebook || $has_custom ) {
			return array(
				'id'            => 'external-comment-systems',
				'title'         => __( 'External Comment System Detected', 'wpshadow' ),
				'description'   => __( 'Using an external comment system adds extra requests and may impact performance. Monitor third-party service uptime.', 'wpshadow' ),
				'severity'      => 'info',
				'category'      => 'monitoring',
				'kb_link'       => 'https://wpshadow.com/kb/external-comment-systems/',
				'training_link' => 'https://wpshadow.com/training/comment-performance/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
			);
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: External Comment Systems
	 * Slug: -external-comment-systems
	 * File: class-diagnostic-external-comment-systems.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: External Comment Systems
	 * Slug: -external-comment-systems
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
	public static function test_live__external_comment_systems(): array {
		$has_disqus   = function_exists( 'dsq_init' );
		$has_facebook = (bool) get_option( 'fb_app_id' );
		$has_custom   = (bool) apply_filters( 'wpshadow_external_comments_detected', false );

		$has_issue = ( $has_disqus || $has_facebook || $has_custom );

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		$test_passes = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'External comment system detection matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (disqus: %s, facebook app id: %s, custom filter: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$has_disqus ? 'yes' : 'no',
				$has_facebook ? 'yes' : 'no',
				$has_custom ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
