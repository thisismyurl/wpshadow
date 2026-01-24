<?php
declare(strict_types=1);
/**
 * RSS Feed Links Diagnostic
 *
 * @package WPShadow
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
 * Check if RSS feed links are in the head when not needed.
  *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_RSS_Feeds extends Diagnostic_Base {

	protected static $slug        = 'rss-feeds';
	protected static $title       = 'RSS Feed Links in Head';
	protected static $description = 'RSS feed links are added to every page head. If you do not use RSS feeds, these can be removed.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if treatment is already applied
		$disabled = get_option( 'wpshadow_rss_feeds_disabled', false );

		if ( $disabled ) {
			return null;
		}

		// Check if feed links are enabled
		$has_feed_links       = has_action( 'wp_head', 'feed_links' ) !== false;
		$has_extra_feed_links = has_action( 'wp_head', 'feed_links_extra' ) !== false;

		if ( ! $has_feed_links && ! $has_extra_feed_links ) {
			return null;
		}

		$links = array();
		if ( $has_feed_links ) {
			$links[] = 'main feed links';
		}
		if ( $has_extra_feed_links ) {
			$links[] = 'extra feed links (comments, categories)';
		}

		return array(
			'id'          => 'rss-feeds',
			'title'       => 'RSS Feed Links in Head',
			'description' => 'Your site outputs ' . implode( ' and ', $links ) . ' in the HTML head. If you don\'t use RSS feeds, removing these reduces HTML size and removes discovery endpoints.',
			'severity'    => 'info',
			'category'    => 'performance',
			'impact'      => 'Adds 2-4 link tags to every page head',
			'fix_time'    => '1 second',
			'kb_article'  => 'rss-feeds',
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: RSS Feed Links in Head
	 * Slug: rss-feeds
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: RSS feed links are added to every page head. If you do not use RSS feeds, these can be removed.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_rss_feeds(): array {
		$disabled = (bool) get_option('wpshadow_rss_feeds_disabled', false);
		$has_feed_links = (has_action('wp_head', 'feed_links') !== false);
		$has_extra_feed_links = (has_action('wp_head', 'feed_links_extra') !== false);

		// Issue exists if: NOT disabled AND (feed_links OR extra_feed_links)
		$has_issue = (!$disabled && ($has_feed_links || $has_extra_feed_links));

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'RSS feeds check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (disabled: %s, feed_links: %s, extra: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$disabled ? 'yes' : 'no',
				$has_feed_links ? 'yes' : 'no',
				$has_extra_feed_links ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}

}

}
