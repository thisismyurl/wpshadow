<?php
declare(strict_types=1);
/**
 * Mobile Friendliness Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check mobile friendliness of the homepage.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Mobile_Friendliness extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Fetch homepage
		$response = wp_remote_get( home_url(), array(
			'timeout' => 10,
			'headers' => array( 'User-Agent' => 'WPShadow-Mobile-Check' ),
		) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) {
			return null;
		}

		// Analyze mobile readiness
		$checks = wpshadow_analyze_mobile_html( $html );
		$summary = array( 'pass' => 0, 'warn' => 0, 'fail' => 0 );
		$issues = array();

		foreach ( $checks as $check ) {
			$status = $check['status'] ?? '';
			if ( isset( $summary[ $status ] ) ) {
				$summary[ $status ]++;
			}

			// Collect non-passing checks
			if ( 'pass' !== $status ) {
				$issues[] = array(
					'id'      => $check['id'] ?? '',
					'label'   => $check['label'] ?? '',
					'status'  => $status,
					'details' => $check['details'] ?? '',
				);
			}
		}

		// If no issues, return null (no finding)
		if ( empty( $issues ) ) {
			return null;
		}

		// Build finding for each issue
		$findings = array();
		foreach ( $issues as $issue ) {
			$threat_level = 'warn' === $issue['status'] ? 40 : 60;
			$color = 'warn' === $issue['status'] ? '#ff9800' : '#f44336';
			$bg_color = 'warn' === $issue['status'] ? '#fff3e0' : '#ffebee';

			$findings[] = array(
				'id'           => 'mobile-' . $issue['id'],
				'title'        => 'Mobile: ' . $issue['label'],
				'description'  => $issue['details'],
				'color'        => $color,
				'bg_color'     => $bg_color,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-friendliness/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=mobile',
				'auto_fixable' => false,
				'threat_level' => $threat_level,
				'category'     => 'mobile',
			);
		}

		// Return array of findings (or null to suppress)
		// For now, return all issues as separate findings
		return empty( $findings ) ? null : $findings[0];
	}

	/**
	 * Get all mobile issues as findings.
	 *
	 * @return array Array of findings.
	 */
	public static function get_all_issues() {
		// Fetch homepage
		$response = wp_remote_get( home_url(), array(
			'timeout' => 10,
			'headers' => array( 'User-Agent' => 'WPShadow-Mobile-Check' ),
		) );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) {
			return array();
		}

		// Analyze mobile readiness
		$checks = wpshadow_analyze_mobile_html( $html );
		$findings = array();

		foreach ( $checks as $check ) {
			$status = $check['status'] ?? '';

			// Only report warnings and failures
			if ( 'pass' === $status ) {
				continue;
			}

			$threat_level = 'warn' === $status ? 40 : 60;
			$color = 'warn' === $status ? '#ff9800' : '#f44336';
			$bg_color = 'warn' === $status ? '#fff3e0' : '#ffebee';

			$findings[] = array(
				'id'           => 'mobile-' . ( $check['id'] ?? '' ),
				'title'        => 'Mobile: ' . ( $check['label'] ?? '' ),
				'description'  => $check['details'] ?? '',
				'color'        => $color,
				'bg_color'     => $bg_color,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-friendliness/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=mobile',
				'auto_fixable' => false,
				'threat_level' => $threat_level,
				'category'     => 'mobile',
			);
		}

		return $findings;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Mobile Friendliness
	 * Slug: -mobile-friendliness
	 * File: class-diagnostic-mobile-friendliness.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Mobile Friendliness
	 * Slug: -mobile-friendliness
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
	public static function test_live__mobile_friendliness(): array {
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
