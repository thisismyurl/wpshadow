<?php
/**
 * Feed Pingback/Trackback Diagnostic
 *
 * Monitors whether pingbacks and trackbacks are enabled in your site's feeds.
 * Pingbacks and trackbacks are comment-like notifications from other sites linking
 * to your content. While useful for community engagement, they can be abused for
 * spam amplification (pingback spam) and Denial of Service attacks.
 *
 * **What This Check Does:**
 * - Reads the `default_ping_status` WordPress option
 * - Returns a finding if pingbacks/trackbacks are enabled
 * - Suggests disabling them unless you actively moderate and value them
 *
 * **Why This Matters:**
 * Attackers exploit pingback features to launch DDoS attacks (Pingback Amplification).
 * Most modern sites have disabled pingbacks entirely. If you don't actively moderate
 * pingback comments, disabling them hardens your site's security profile significantly.
 *
 * **Who Should Care:**
 * - Security-conscious admins: Reduces attack surface
 * - High-traffic sites: Prevents pingback spam overwhelming moderation queues
 * - Agency administrators: One less thing to monitor and moderate
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Explains why this matters, not just that it's "bad"
 * - #8 Inspire Confidence: Hardening security through proven best practice
 * - #9 Show Value: Reduces spam moderation workload
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/feed-pingback-trackback for detailed explanation
 * or https://wpshadow.com/training/feed-security for interactive walkthrough.
 *
 * @since   1.6032.1921
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Feed_Pingback_Trackback Class
 *
 * Monitors feed security configuration, specifically whether pingbacks/trackbacks
 * are enabled. This diagnostic extends the core `Diagnostic_Base` and follows
 * the standard WPShadow pattern: detect issue, provide context, suggest fix.
 *
 * **Implementation Pattern:**
 * 1. Read WordPress core option: `default_ping_status`
 * 2. Check value ('open' = enabled, 'closed' = disabled)
 * 3. If enabled, return finding with recommendation
 * 4. Otherwise return null (no issue found)
 *
 * **Related Diagnostics:**
 * - Feed XML Validity: Checks that feed is properly formatted
 * - Feed Content Length: Monitors feed excerpt vs full content
 * - Feed URL Accessibility: Ensures feed URLs respond correctly
 *
 * @since 1.6032.1921
 */
class Diagnostic_Feed_Pingback_Trackback extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-pingback-trackback';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Pingback/Trackback';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pingbacks and trackbacks are enabled in feeds.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1921
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$pingbacks = get_option( 'default_ping_status', 'open' );
		if ( 'open' === $pingbacks ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Pingbacks and trackbacks are enabled in feeds. Consider disabling for security.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 40,
				'auto_fixable'=> true,
				'kb_link'     => 'https://wpshadow.com/kb/feed-pingback-trackback',
			);
		}
		return null;
	}
}
