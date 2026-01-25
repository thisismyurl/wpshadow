<?php

declare(strict_types=1);
/**
 * Content Security Policy Diagnostic
 *
 * Philosophy: XSS prevention - control resource loading
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if Content Security Policy is configured.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_CSP extends Diagnostic_Base {

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

		if ( empty( $headers['content-security-policy'] ) && empty( $headers['content-security-policy-report-only'] ) ) {
			return array(
				'id'            => 'csp-header',
				'title'         => 'Content Security Policy Not Configured',
				'description'   => 'Your site lacks a Content Security Policy (CSP) header, which helps prevent XSS attacks by controlling which resources can be loaded. Consider implementing CSP.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/implement-content-security-policy/',
				'training_link' => 'https://wpshadow.com/training/csp-security/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: CSP
	 * Slug: -csp
	 * File: class-diagnostic-csp.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: CSP
	 * Slug: -csp
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
	public static function test_live__csp(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		$response = wp_remote_head(
			home_url(),
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		$has_csp = false;

		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );

			if ( ! empty( $headers['content-security-policy'] ) || ! empty( $headers['content-security-policy-report-only'] ) ) {
				$has_csp = true;
			}
		}

		$expected_issue = ! $has_csp;

		$result      = self::check();
		$has_finding = is_array( $result );

		if ( $expected_issue === $has_finding ) {
			$message = $expected_issue ? 'Finding returned when CSP header missing.' : 'No finding when CSP header present.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $expected_issue
			? 'Expected finding for missing CSP but got none.'
			: 'Expected no finding when CSP present but got a finding.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
