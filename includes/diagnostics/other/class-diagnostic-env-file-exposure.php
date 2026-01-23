<?php
declare(strict_types=1);
/**
 * Environment File Exposure Diagnostic
 *
 * Philosophy: Credential protection - hide .env files
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if .env files are web-accessible.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_ENV_File_Exposure extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test if .env is accessible
		$env_url  = trailingslashit( home_url() ) . '.env';
		$response = wp_remote_get(
			$env_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		// If .env is accessible and contains environment variables
		if ( $status_code === 200 &&
			( strpos( $body, 'DB_PASSWORD' ) !== false ||
				strpos( $body, 'API_KEY' ) !== false ||
				strpos( $body, '=' ) !== false ) ) {

			return array(
				'id'            => 'env-file-exposure',
				'title'         => 'Environment File Publicly Accessible',
				'description'   => 'Your .env file is accessible via web browser, exposing database credentials, API keys, and other secrets. Block access to .env files immediately via .htaccess or server configuration.',
				'severity'      => 'critical',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/protect-env-files/',
				'training_link' => 'https://wpshadow.com/training/environment-security/',
				'auto_fixable'  => true,
				'threat_level'  => 80,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: ENV File Exposure
	 * Slug: -env-file-exposure
	 * File: class-diagnostic-env-file-exposure.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: ENV File Exposure
	 * Slug: -env-file-exposure
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
	public static function test_live__env_file_exposure(): array {
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
