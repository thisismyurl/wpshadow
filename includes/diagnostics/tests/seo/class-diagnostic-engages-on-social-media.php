<?php
/**
 * Diagnostic: Social Media Engagement
 *
 * Tests if site actively engages on social media platforms.
 * Social media extends reach and builds brand awareness.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Media Engagement Diagnostic Class
 *
 * Checks if site has social media integration and encourages
 * social sharing and engagement.
 *
 * Detection methods:
 * - Social sharing buttons
 * - Social media links
 * - Social auto-posting
 * - Social feed integration
 *
 * @since 1.7034.1430
 */
class Diagnostic_Engages_On_Social_Media extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'engages-on-social-media';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Engagement';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site actively engages on social media platforms';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Social sharing buttons present
	 * - 1 point: Social media auto-posting enabled
	 * - 1 point: Social media feeds embedded
	 * - 1 point: Click to Tweet or similar features
	 * - 1 point: Open Graph tags configured
	 *
	 * @since  1.7034.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check for social sharing plugins.
		$sharing_plugins = array(
			'social-warfare/social-warfare.php'          => 'Social Warfare',
			'sassy-social-share/sassy-social-share.php'  => 'Sassy Social Share',
			'addtoany/addtoany.php'                      => 'AddToAny',
			'jetpack/jetpack.php'                        => 'Jetpack Sharing',
			'shared-counts/shared-counts.php'            => 'Shared Counts',
		);

		foreach ( $sharing_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['sharing_plugin'] = $name;
				break;
			}
		}

		// Check for social auto-posting plugins.
		$autopost_plugins = array(
			'jetpack/jetpack.php'                        => 'Jetpack Publicize',
			'blog2social/blog2social.php'                => 'Blog2Social',
			'wp-to-twitter/wp-to-twitter.php'            => 'WP to Twitter',
			'revive-old-post/revive-old-post.php'        => 'Revive Old Posts',
		);

		foreach ( $autopost_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['autopost_plugin'] = $name;
				break;
			}
		}

		// Check for social feed plugins.
		$feed_plugins = array(
			'custom-facebook-feed/custom-facebook-feed.php' => 'Facebook Feed',
			'instagram-feed/instagram-feed.php'          => 'Instagram Feed',
			'custom-twitter-feeds/custom-twitter-feed.php' => 'Twitter Feed',
			'feeds-for-youtube/youtube-feed.php'         => 'YouTube Feed',
		);

		foreach ( $feed_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['social_feed'] = $name;
				break;
			}
		}

		// Check for Click to Tweet or similar.
		$ctt_plugins = array(
			'click-to-tweet-by-todaymade/click-to-tweet.php' => 'Click to Tweet',
			'better-click-to-tweet/better-click-to-tweet.php' => 'Better Click to Tweet',
		);

		foreach ( $ctt_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['click_to_tweet'] = $name;
				break;
			}
		}

		// Check for Open Graph tags (SEO plugins often include this).
		$og_enabled = false;
		
		// Check Yoast SEO.
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			$og_enabled = true;
		}
		
		// Check All in One SEO.
		if ( is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
			$og_enabled = true;
		}
		
		// Check RankMath.
		if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			$og_enabled = true;
		}

		if ( $og_enabled ) {
			$score++;
			$details['open_graph'] = true;
		}

		// Calculate percentage score.
		$percentage = ( $score / $max_score ) * 100;

		// Pass if score is 60% or higher.
		if ( $percentage >= 60 ) {
			return null;
		}

		// Build finding.
		$severity     = $percentage < 30 ? 'low' : 'info';
		$threat_level = (int) ( 45 - $percentage );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage score */
				__( 'Social media engagement score: %d%%. Social sharing extends your reach and builds brand awareness.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/social-media-engagement',
			'details'      => $details,
			'why_matters'  => self::get_why_matters(),
		);
	}

	/**
	 * Get the "Why This Matters" educational content.
	 *
	 * @since  1.7034.1430
	 * @return string Explanation of why this diagnostic matters.
	 */
	private static function get_why_matters() {
		return __(
			'Social media amplifies your content\'s reach far beyond your website. When readers share your content, you reach their networks. Auto-posting ensures every new article gets promoted. Social feeds show you\'re active and engaged. Open Graph tags control how your content looks when shared, increasing click-through rates. Social media isn\'t optional anymore—it\'s where conversations happen.',
			'wpshadow'
		);
	}
}
