<?php

declare(strict_types=1);
/**
 * CORS Misconfiguration Diagnostic
 *
 * Philosophy: API security - prevent cross-origin data leakage
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for insecure CORS configuration.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_CORS_Misconfiguration extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Test REST API endpoint for CORS headers
		$rest_url = rest_url();
		$response = wp_remote_get($rest_url, array(
			'timeout' => 5,
			'sslverify' => false,
			'headers' => array('Origin' => 'https://evil.example.com')
		));

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		// Check for wildcard CORS
		if (
			! empty($headers['access-control-allow-origin']) &&
			$headers['access-control-allow-origin'] === '*'
		) {

			// Check if credentials are also allowed (critical vulnerability)
			$allows_credentials = ! empty($headers['access-control-allow-credentials']) &&
				$headers['access-control-allow-credentials'] === 'true';

			$severity = $allows_credentials ? 'critical' : 'high';
			$threat = $allows_credentials ? 85 : 70;

			return array(
				'id'          => 'cors-misconfiguration',
				'title'       => 'Insecure CORS Configuration',
				'description' => sprintf(
					'Your REST API has Access-Control-Allow-Origin set to wildcard (*). %s Restrict CORS to specific trusted domains.',
					$allows_credentials ? 'Combined with credentials=true, this allows ANY site to steal authenticated data.' : 'This allows any website to access your API.'
				),
				'severity'    => $severity,
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-cors-configuration/',
				'training_link' => 'https://wpshadow.com/training/cors-security/',
				'auto_fixable' => true,
				'threat_level' => $threat,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: CORS Misconfiguration
	 * Slug: -cors-misconfiguration
	 * File: class-diagnostic-cors-misconfiguration.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: CORS Misconfiguration
	 * Slug: -cors-misconfiguration
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
	public static function test_live__cors_misconfiguration(): array
	{
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		$rest_url = rest_url();
		$response = wp_remote_get($rest_url, array(
			'timeout' => 5,
			'sslverify' => false,
			'headers' => array('Origin' => 'https://evil.example.com')
		));

		$has_insecure_cors = false;

		if (! is_wp_error($response)) {
			$headers = wp_remote_retrieve_headers($response);

			if (
				! empty($headers['access-control-allow-origin']) &&
				$headers['access-control-allow-origin'] === '*'
			) {
				$has_insecure_cors = true;
			}
		}

		$result = self::check();
		$has_finding = is_array($result);

		if ($has_insecure_cors === $has_finding) {
			$message = $has_insecure_cors ? 'Finding returned when wildcard CORS detected.' : 'No finding when CORS is secure.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $has_insecure_cors
			? 'Expected finding for wildcard CORS but got none.'
			: 'Expected no finding but CORS wildcard was detected.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
