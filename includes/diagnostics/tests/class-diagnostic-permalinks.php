<?php
declare(strict_types=1);
/**
 * Permalink Diagnostic
 *
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check permalink structure configuration.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Permalinks extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! self::is_permalink_configured() ) {
			return array(
				'id'           => 'permalinks-plain',
				'title'        => 'Permalink Structure Not Set',
				'description'  => 'Your site is using plain permalinks (/?p=123). This hurts SEO and user experience. Switch to a prettier structure.',
				'kb_link'      => 'https://wpshadow.com/kb/configure-wordpress-permalinks-for-seo/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=permalinks',
				'action_link'  => admin_url( 'options-permalink.php' ),
				'action_text'  => 'Fix Permalinks',
				'auto_fixable' => true,
				'threat_level' => 30,
			);
		}

		return null;
	}

	/**
	 * Check if permalinks are properly configured.
	 *
	 * @return bool True if permalinks are set, false if plain.
	 */
	private static function is_permalink_configured() {
		$structure = get_option( 'permalink_structure', '' );
		return ! empty( $structure );
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Permalinks
	 * Slug: -permalinks
	 * File: class-diagnostic-permalinks.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Permalinks
	 * Slug: -permalinks
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
	public static function test_live__permalinks(): array {
		$structure            = get_option( 'permalink_structure', '' );
		$expected_issue       = empty( $structure );
		$diagnostic_result    = self::check();
		$diagnostic_has_issue = ( null !== $diagnostic_result );
		$test_passes          = ( $expected_issue === $diagnostic_has_issue );

		$message = sprintf(
			'Permalink structure: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			empty( $structure ) ? 'PLAIN' : $structure,
			$expected_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
