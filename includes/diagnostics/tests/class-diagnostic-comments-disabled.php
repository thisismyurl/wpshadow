<?php

declare(strict_types=1);
/**
 * Comments Disabled Diagnostic
 *
 * Detects when comments are disabled and suggests removing the comments menu
 * from the admin sidebar for cleaner UX.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Diagnostic for comments being disabled
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Comments_Disabled extends Diagnostic_Base
{

	protected static $slug        = 'comments-disabled';
	protected static $title       = 'Comments Disabled';
	protected static $description = 'Detects when comments are disabled and suggests removing the comments menu from admin.';

	/**
	 * Check if comments are disabled and menu is still visible
	 */
	public static function check(): ?array
	{
		$default_comment_status = get_option('default_comment_status');

		// Only report if comments are closed
		if ('closed' !== $default_comment_status) {
			return null;
		}

		// Check if comments menu hiding is already enabled
		if (get_option('wpshadow_hide_comments_menu')) {
			return null;
		}

		$description  = __('Comments are disabled by default, but the WordPress comments menu is still visible in the admin sidebar. This can be hidden for a cleaner admin interface. WPShadow can automatically remove this menu.', 'wpshadow');
		$description .= '<br><br>' . __('Tip: When comments are disabled, WPShadow also recommends removing the "Howdy" greeting for a professional admin experience.', 'wpshadow');

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'category'     => 'admin-ux',
			'severity'     => 'low',
			'threat_level' => 5,
			'auto_fixable' => true,
			'timestamp'    => current_time('mysql'),
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Comments Disabled
	 * Slug: comments-disabled
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Detects when comments are disabled and suggests removing the comments menu from admin.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_comments_disabled(): array
	{
		$default_comment_status = get_option('default_comment_status');
		$hide_menu_enabled = (bool) get_option('wpshadow_hide_comments_menu');

		// Issue exists if: comments closed AND menu not hidden
		$has_issue = ($default_comment_status === 'closed' && !$hide_menu_enabled);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Comments disabled check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (default_comment_status: %s, hide_menu: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$default_comment_status,
				$hide_menu_enabled ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
