<?php
declare(strict_types=1);
/**
 * Referrer Policy Diagnostic
 *
 * Philosophy: Privacy protection - control referrer information
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if Referrer Policy is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Referrer_Policy extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$response = wp_remote_head(
			home_url(),
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$headers = wp_remote_retrieve_headers( $response );

		if ( empty( $headers['referrer-policy'] ) ) {
			return array(
				'id'            => 'referrer-policy',
				'title'         => 'Referrer Policy Not Set',
				'description'   => 'Your site lacks a Referrer-Policy header, which may leak sensitive URL parameters to third-party sites. Set a restrictive referrer policy like "no-referrer" or "same-origin".',
				'severity'      => 'low',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/set-referrer-policy/',
				'training_link' => 'https://wpshadow.com/training/referrer-policy/',
				'auto_fixable'  => true,
				'threat_level'  => 40,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Referrer Policy
	 * Slug: -referrer-policy
	 * File: class-diagnostic-referrer-policy.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Referrer Policy
	 * Slug: -referrer-policy
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
	public static function test_live__referrer_policy(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}
