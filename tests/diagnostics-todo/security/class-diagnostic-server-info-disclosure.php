<?php
declare(strict_types=1);
/**
 * Server Information Disclosure Diagnostic
 *
 * Philosophy: Information security - hide server details
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if server reveals version information.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Server_Info_Disclosure extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$response = wp_remote_head( home_url(), array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$headers = wp_remote_retrieve_headers( $response );
		$disclosed_headers = array();
		
		// Check for revealing headers
		if ( ! empty( $headers['server'] ) && $headers['server'] !== 'nginx' && $headers['server'] !== 'Apache' ) {
			$disclosed_headers[] = 'Server: ' . $headers['server'];
		}
		
		if ( ! empty( $headers['x-powered-by'] ) ) {
			$disclosed_headers[] = 'X-Powered-By: ' . $headers['x-powered-by'];
		}
		
		if ( ! empty( $disclosed_headers ) ) {
			return array(
				'id'          => 'server-info-disclosure',
				'title'       => 'Server Information Disclosure',
				'description' => sprintf(
					'Your server reveals version information: %s. Remove or obscure these headers to prevent targeted attacks.',
					implode( ', ', $disclosed_headers )
				),
				'severity'    => 'low',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/hide-server-information/',
				'training_link' => 'https://wpshadow.com/training/server-hardening/',
				'auto_fixable' => true,
				'threat_level' => 45,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Server Info Disclosure
	 * Slug: -server-info-disclosure
	 * File: class-diagnostic-server-info-disclosure.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Server Info Disclosure
	 * Slug: -server-info-disclosure
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
	public static function test_live__server_info_disclosure(): array {
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
