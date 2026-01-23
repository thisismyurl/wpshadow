<?php
declare(strict_types=1);
/**
 * GraphQL Introspection Diagnostic
 *
 * Philosophy: API security - disable introspection in production
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if GraphQL introspection is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_GraphQL_Introspection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if WPGraphQL is active
		if ( ! class_exists( 'WPGraphQL' ) ) {
			return null; // GraphQL not installed
		}

		// Test introspection query
		$graphql_endpoint    = trailingslashit( home_url() ) . 'graphql';
		$introspection_query = '{"query":"{__schema{types{name}}}"}';

		$response = wp_remote_post(
			$graphql_endpoint,
			array(
				'timeout'   => 5,
				'headers'   => array( 'Content-Type' => 'application/json' ),
				'body'      => $introspection_query,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// If introspection returns schema
		if ( ! empty( $data['data']['__schema'] ) ) {
			return array(
				'id'            => 'graphql-introspection',
				'title'         => 'GraphQL Introspection Enabled',
				'description'   => 'Your GraphQL API allows introspection, revealing the entire schema including types, fields, and mutations. This helps attackers discover all API capabilities. Disable introspection in production.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-graphql-introspection/',
				'training_link' => 'https://wpshadow.com/training/graphql-security/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: GraphQL Introspection
	 * Slug: -graphql-introspection
	 * File: class-diagnostic-graphql-introspection.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: GraphQL Introspection
	 * Slug: -graphql-introspection
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
	public static function test_live__graphql_introspection(): array {
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
