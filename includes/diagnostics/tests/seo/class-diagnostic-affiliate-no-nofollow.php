<?php
/**
 * Diagnostic: Missing Nofollow on Affiliate Links
 *
 * Detects affiliate links without rel="nofollow" or rel="sponsored", which
 * violates FTC guidelines and risks Google penalties.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Affiliate No Nofollow Diagnostic Class
 *
 * Checks for proper affiliate link attributes.
 *
 * Detection methods:
 * - Affiliate URL pattern matching
 * - rel attribute verification
 * - Affiliate link plugins
 *
 * @since 0.6093.1200
 */
class Diagnostic_Affiliate_No_Nofollow extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'affiliate-no-nofollow';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Nofollow on Affiliate Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'FTC violation, Google penalty risk - Must use rel="sponsored"';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'external-linking';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Affiliate link plugin installed
	 * - 2 points: No affiliate links without proper rel
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score                = 0;
		$max_score            = 4;
		$has_affiliate_plugin = false;
		$problem_links        = array();

		// Check for affiliate link management plugins.
		$affiliate_plugins = array(
			'thirstyaffiliates/thirstyaffiliates.php'   => 'ThirstyAffiliates',
			'pretty-links/pretty-links.php'             => 'Pretty Links',
			'affiliate-wp/affiliate-wp.php'             => 'AffiliateWP',
		);

		foreach ( $affiliate_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score               += 2;
				$has_affiliate_plugin = true;
				break;
			}
		}

		// Common affiliate URL patterns.
		$affiliate_patterns = array(
			'amazon.com/',
			'amzn.to/',
			'shareasale.com/',
			'clickbank.',
			'jvzoo.com/',
			'warrior.com/aff',
			'affiliate',
			'?ref=',
			'?aff=',
			'/ref/',
			'/aff/',
		);

		// Get sample posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$affiliate_links_found = 0;
		$links_without_nofollow = 0;

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Extract all links.
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\']([^>]*)>/i', $content, $matches, PREG_SET_ORDER );

			foreach ( $matches as $match ) {
				$url        = $match[1];
				$attributes = $match[2];

				// Check if URL matches affiliate patterns.
				$is_affiliate = false;
				foreach ( $affiliate_patterns as $pattern ) {
					if ( stripos( $url, $pattern ) !== false ) {
						$is_affiliate = true;
						break;
					}
				}

				if ( ! $is_affiliate ) {
					continue;
				}

				$affiliate_links_found++;

				// Check for rel="nofollow" or rel="sponsored".
				$has_proper_rel = false;
				if (
					stripos( $attributes, 'rel="nofollow"' ) !== false ||
					stripos( $attributes, 'rel="sponsored"' ) !== false ||
					stripos( $attributes, 'rel=\'nofollow\'' ) !== false ||
					stripos( $attributes, 'rel=\'sponsored\'' ) !== false
				) {
					$has_proper_rel = true;
				}

				if ( ! $has_proper_rel ) {
					$links_without_nofollow++;
					if ( count( $problem_links ) < 10 ) {
						$problem_links[] = array(
							'post_id'   => $post->ID,
							'post_title' => $post->post_title,
							'url'       => $url,
							'post_url'  => get_permalink( $post->ID ),
						);
					}
				}
			}
		}

		if ( $affiliate_links_found === 0 ) {
			// No affiliate links to check.
			return null;
		}

		if ( $links_without_nofollow === 0 ) {
			$score += 2;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of links without nofollow, 2: total affiliate links */
				__( 'Found %1$d affiliate links (out of %2$d) without proper rel attribute. CRITICAL: This violates FTC guidelines and Google\'s Webmaster Guidelines. Consequences: FTC fines ($10,000-$43,000 per violation), Google manual penalties (rankings drop), Loss of trust (visitors and search engines), Legal liability (undisclosed paid links). Required attributes: rel="sponsored" (Google\'s official recommendation for affiliate/paid links), rel="nofollow" (older standard, still acceptable), Both: rel="nofollow sponsored" (belt + suspenders). Also required: Clear disclosure ("This post contains affiliate links"), Above-the-fold disclosure (FTC requirement), Plain language ("I earn commission"). Affiliate link plugins (ThirstyAffiliates, Pretty Links) auto-add proper attributes.', 'wpshadow' ),
				$links_without_nofollow,
				$affiliate_links_found
			),
			'severity'      => 'critical',
			'threat_level'  => 70,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/affiliate-no-nofollow?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'problem_links' => $problem_links,
			'stats'         => array(
				'affiliate_links_found'   => $affiliate_links_found,
				'without_nofollow'        => $links_without_nofollow,
				'has_affiliate_plugin'    => $has_affiliate_plugin,
			),
			'recommendation' => __( 'Install ThirstyAffiliates or Pretty Links to auto-manage affiliate links. Manually add rel="sponsored" to existing affiliate links. Add disclosure statement at top of posts with affiliate links. Review FTC guidelines.', 'wpshadow' ),
		);
	}
}
