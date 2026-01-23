<?php
declare(strict_types=1);
/**
 * Webhooks Readiness Diagnostic
 *
 * Philosophy: Encourage automation and integrations; points to Pro workflows.
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if REST API and permalinks are ready for webhook-based workflows.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Webhooks_Readiness extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();

		// Check REST API availability
		$response = wp_remote_get(
			rest_url(),
			array(
				'timeout'   => 6,
				'sslverify' => false,
			)
		);
		if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) >= 400 ) {
			$issues[] = 'REST API not reachable; webhooks and integrations may fail';
		}

		// Check permalinks (pretty permalinks recommended for REST)
		$permalink_structure = get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) ) {
			$issues[] = 'Pretty permalinks are disabled; enable them for reliable webhook endpoints';
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => 'webhooks-readiness',
			'title'        => 'Webhooks Not Ready',
			'description'  => implode( '. ', $issues ) . '.',
			'severity'     => 'medium',
			'category'     => 'workflows',
			'kb_link'      => 'https://wpshadow.com/kb/enable-wordpress-rest-api/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=webhooks',
			'auto_fixable' => false,
			'threat_level' => 40,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Webhooks Readiness
	 * Slug: -webhooks-readiness
	 * File: class-diagnostic-webhooks-readiness.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Webhooks Readiness
	 * Slug: -webhooks-readiness
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
	public static function test_live__webhooks_readiness(): array {
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
