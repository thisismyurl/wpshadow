<?php
declare(strict_types=1);
/**
 * Diagnostic: Howdy Greeting Detection
 *
 * Detects if the "Howdy" admin greeting is displayed in the top menu.
 * Provides an option to remove it for a cleaner admin interface.
 *
 * @package WPShadow
 * @subpackage Diagnostics
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Howdy_Greeting
 *
 * Detects the "Howdy" greeting in WordPress admin top menu.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Howdy_Greeting extends Diagnostic_Base {

	protected static $slug        = 'howdy-greeting-visible';
	protected static $title       = 'Admin Greeting Visible';
	protected static $description = 'Detects if the "Howdy" greeting is displayed in the admin top menu.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding array or null if no issues detected.
	 */
	public static function check(): ?array {
		// Check if "Howdy" greeting is enabled (by default it is)
		$howdy_hidden = get_option( 'wpshadow_hide_howdy_greeting', false );

		if ( $howdy_hidden ) {
			// Already configured to hide, no finding
			return null;
		}

		// "Howdy" is displayed by default - this is just informational
		$finding = array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => self::build_description(),
			'category'     => 'admin-ux',
			'severity'     => 'info',
			'threat_level' => 1,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);

		return $finding;
	}

	/**
	 * Build the finding description with recommendations.
	 *
	 * @return string HTML description.
	 */
	private static function build_description(): string {
		$description  = __( 'The "Howdy" admin greeting is currently displayed in the top menu bar.', 'wpshadow' );
		$description .= '<br><br><strong>' . __( 'Options:', 'wpshadow' ) . '</strong><ul>';
		$description .= '<li>' . __( 'Keep it for a friendly admin experience', 'wpshadow' ) . '</li>';
		$description .= '<li>' . __( 'Remove it for a cleaner, more professional admin interface', 'wpshadow' ) . '</li>';
		$description .= '<li>' . __( 'This is automatically hidden when comments are disabled site-wide', 'wpshadow' ) . '</li>';
		$description .= '</ul>';

		return $description;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Admin Greeting Visible
	 * Slug: howdy-greeting-visible
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Detects if the
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_howdy_greeting_visible(): array {
		$howdy_hidden = (bool) get_option('wpshadow_hide_howdy_greeting', false);
		$has_issue = !$howdy_hidden;

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Howdy greeting check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (howdy_hidden: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$howdy_hidden ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}

}
