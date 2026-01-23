<?php
declare(strict_types=1);
/**
 * Exposed .git Directory Diagnostic
 *
 * Philosophy: Source control security - protect repository data
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if .git directory is web-accessible.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Git_Directory_Exposed extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test if .git/config is accessible
		$git_config_url = trailingslashit( home_url() ) . '.git/config';
		$response = wp_remote_get( $git_config_url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$status = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		
		// Check if .git directory is accessible
		if ( $status === 200 && 
		     ( strpos( $body, '[core]' ) !== false || 
		       strpos( $body, '[remote' ) !== false ) ) {
			
			return array(
				'id'          => 'git-directory-exposed',
				'title'       => '.git Directory Publicly Accessible',
				'description' => 'Your .git directory is accessible via web browser, exposing complete source code history including deleted files, credentials in old commits, and development secrets. Block .git access via .htaccess immediately.',
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/protect-git-directory/',
				'training_link' => 'https://wpshadow.com/training/source-control-security/',
				'auto_fixable' => true,
				'threat_level' => 90,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Git Directory Exposed
	 * Slug: -git-directory-exposed
	 * File: class-diagnostic-git-directory-exposed.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Git Directory Exposed
	 * Slug: -git-directory-exposed
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
	public static function test_live__git_directory_exposed(): array {
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
