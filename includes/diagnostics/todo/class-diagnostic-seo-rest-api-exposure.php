<?php
declare(strict_types=1);
/**
 * REST API Exposure Diagnostic
 *
 * Philosophy: Limit unnecessary API endpoints
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_REST_API_Exposure extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-rest-api-exposure',
            'title' => 'REST API Endpoint Exposure',
            'description' => 'Review REST API endpoints and disable unnecessary ones to reduce attack surface and info leakage.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/rest-api-security/',
            'training_link' => 'https://wpshadow.com/training/wordpress-security/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO REST API Exposure
	 * Slug: -seo-rest-api-exposure
	 * File: class-diagnostic-seo-rest-api-exposure.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO REST API Exposure
	 * Slug: -seo-rest-api-exposure
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
	public static function test_live__seo_rest_api_exposure(): array {
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
