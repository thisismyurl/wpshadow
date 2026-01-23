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

}