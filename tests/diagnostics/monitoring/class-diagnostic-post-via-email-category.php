<?php
declare(strict_types=1);
/**
 * Post via Email Category Diagnostic
 *
 * Flags when Post via Email posts are routed to the default "Uncategorized" category.
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check Post via Email default category alignment.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Post_Via_Email_Category extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue or feature disabled.
	 */
	public static function check(): ?array {
		$post_via_email = get_option( 'mailserver_url' );
		if ( empty( $post_via_email ) ) {
			return null;
		}

		$default_category = get_option( 'default_post_category' );
		if ( '1' !== $default_category ) { // 1 is Uncategorized.
			return null;
		}

		return array(
			'id'           => 'post-via-email-default-category',
			'title'        => 'Post via Email Uses "Uncategorized"',
			'description'  => 'Post via Email is enabled but the default post category is "Uncategorized". Assign a dedicated category to prevent unorganized content.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'category'     => 'settings',
			'auto_fixable' => false,
			'kb_link'      => 'https://wordpress.org/support/article/post-via-email/',
			'threat_level' => 12,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Post Via Email Category
	 * Slug: -post-via-email-category
	 * File: class-diagnostic-post-via-email-category.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Post Via Email Category
	 * Slug: -post-via-email-category
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
	public static function test_live__post_via_email_category(): array {
		$post_via_email = get_option('mailserver_url');
		$default_category = get_option('default_post_category');

		$has_issue = (!empty($post_via_email) && $default_category === '1');

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Post by email default category detection matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (mailserver_url: %s, default_post_category: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$post_via_email !== false ? (string) $post_via_email : 'n/a',
				$default_category !== false ? (string) $default_category : 'n/a'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}

}
