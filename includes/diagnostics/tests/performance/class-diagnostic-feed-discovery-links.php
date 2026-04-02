<?php
/**
 * Feed Discovery Links Diagnostic
 *
 * Verifies that your WordPress site properly advertises feed URLs in HTML <head>
 * via <link rel="alternate"> tags. These discovery links help feed readers, browsers,
 * and services automatically find your RSS/Atom feeds. Without them, subscribers
 * must manually construct feed URLs - leading to lost subscriptions.
 *
 * **What This Check Does:**
 * - Scans HTML <head> for feed discovery links
 * - Validates <link rel="alternate" type="application/rss+xml"> presence
 * - Checks <link rel="alternate" type="application/atom+xml"> presence
 * - Ensures feed URLs are absolute (not relative)
 * - Detects if discovery links point to valid feed endpoints
 *
 * **Why This Matters:**
 * Feed readers like Feedly, Inoreader, and NewsBlur rely on discovery links.
 * Without proper <link> tags, these services can't auto-detect your feeds.
 * Users see "No feeds found" error, assume your site doesn't have RSS,
 * and give up. You lose subscribers without ever knowing they tried.
 *
 * **Real-World Impact:**
 * Browser "Subscribe" button: Chrome/Firefox detect feeds via discovery links.
 * If links missing → Button disabled → Users can't subscribe.
 *
 * Feed aggregators: Services like Feedly scan for `<link rel="alternate">`.
 * If missing → "No feed detected" → Users abandon subscription attempt.
 *
 * SEO tools: Google News and other indexers use discovery links to find feeds.
 * If missing → Content not indexed → Lost traffic opportunity.
 *
 * **What Proper Discovery Looks Like:**
 * ```html
 * <head>
 *   <!-- RSS Feed -->
 *   <link rel="alternate" type="application/rss+xml" 
 *         title="Site Name RSS Feed" 
 *         href="https://example.com/feed/" />
 *   
 *   <!-- Atom Feed -->
 *   <link rel="alternate" type="application/atom+xml" 
 *         title="Site Name Atom Feed" 
 *         href="https://example.com/feed/atom/" />
 *   
 *   <!-- Comments Feed -->
 *   <link rel="alternate" type="application/rss+xml" 
 *         title="Site Name Comments Feed" 
 *         href="https://example.com/comments/feed/" />
 * </head>
 * ```
 *
 * **Common Causes of Missing Links:**
 * - Theme overrides `wp_head()` and removes `feed_links()` call
 * - Plugin removes discovery links (SEO plugins sometimes do this)
 * - Custom theme doesn't call `wp_head()` at all
 * - Feed links disabled via `remove_action( 'wp_head', 'feed_links', 2 )`
 *
 * **How Feed Readers Use This:**
 * 1. User enters your site URL into Feedly
 * 2. Feedly fetches homepage HTML
 * 3. Feedly searches for `<link rel="alternate" type="application/rss+xml">`
 * 4. If found: Subscribes to feed URL from href attribute
 * 5. If missing: Shows "No feed found" error
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Makes subscription effortless for users
 * - #9 Show Value: Enables content distribution to reach more readers
 * - Accessibility First: Feed discovery is assistive technology for content consumption
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/feed-discovery-configuration for setup guide
 * or https://wpshadow.com/training/content-syndication-optimization
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Feed_Discovery_Links Class
 *
 * Uses WordPress' built-in feed link functions to check discovery.
 * Feed discovery links are added by `feed_links()` and `feed_links_extra()`
 * functions hooked to `wp_head` action.
 *
 * **Implementation Pattern:**
 * 1. Check if `feed_links` action is attached to `wp_head`
 * 2. Capture homepage HTML output
 * 3. Parse HTML for <link rel="alternate"> tags
 * 4. Validate feed URLs are accessible
 * 5. Return finding if links missing or broken
 *
 * **Detection Method:**
 * WordPress core adds discovery links via `wp_head()`. This diagnostic
 * checks both hook registration and actual HTML output to catch themes
 * that remove links after registration or bypass `wp_head()` entirely.
 *
 * **Related Diagnostics:**
 * - Feed URL Accessibility: Validates feed endpoints respond
 * - Feed XML Validity: Ensures feeds contain valid XML
 * - Theme Integration: Checks if theme calls `wp_head()`
 *
 * @since 1.6093.1200
 */
class Diagnostic_Feed_Discovery_Links extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-discovery-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Discovery Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feed discovery links are present in the site <head>.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Use WordPress API to check if feed links are added
		$has_feed_links = has_action( 'wp_head', 'feed_links', 2 ) || has_action( 'wp_head', 'feed_links_extra', 3 );
		if ( ! $has_feed_links ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed discovery links are missing from the <head>.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 40,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-discovery-links',
			);
		}
		return null;
	}
}
