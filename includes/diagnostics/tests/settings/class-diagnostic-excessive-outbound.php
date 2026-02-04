<?php
/**
 * Diagnostic: Too Many Outbound Links
 *
 * Detects excessive external linking (20+ links in 1,000 words) which
 * sends PageRank away and dilutes topical focus.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1512
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Excessive Outbound Links Diagnostic Class
 *
 * Checks for too many external links relative to content length.
 *
 * Detection methods:
 * - External link counting
 * - Link-to-word ratio analysis
 * - Link distribution assessment
 *
 * @since 1.7030.1512
 */
class Diagnostic_Excessive_Outbound extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'excessive-outbound';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Too Many Outbound Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = '20+ external links in 1,000 words sends PageRank away';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'external-linking';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 3 points: <10% posts with excessive outbound links
	 * - 2 points: <20% with excessive links
	 * - 0 points: ≥20% with excessive links
	 *
	 * @since  1.7030.1512
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score                      = 0;
		$max_score                  = 3;
		$posts_with_excessive_links = 0;
		$problem_posts              = array();

		// Get sample posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 40,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		foreach ( $posts as $post ) {
			$content    = $post->post_content;
			$word_count = str_word_count( wp_strip_all_tags( $content ) );

			// Skip very short posts.
			if ( $word_count < 300 ) {
				continue;
			}

			// Extract external links.
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches );

			$external_link_count = 0;

			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Skip internal links.
					if ( strpos( $url, home_url() ) !== false || strpos( $url, '/' ) === 0 ) {
						continue;
					}

					// Skip anchors.
					if ( strpos( $url, '#' ) === 0 ) {
						continue;
					}

					$external_link_count++;
				}
			}

			// Calculate links per 1,000 words.
			$links_per_1k = ( $external_link_count / $word_count ) * 1000;

			// Flag if >20 external links per 1,000 words.
			if ( $links_per_1k > 20 ) {
				$posts_with_excessive_links++;
				if ( count( $problem_posts ) < 10 ) {
					$problem_posts[] = array(
						'post_id'      => $post->ID,
						'title'        => $post->post_title,
						'word_count'   => $word_count,
						'external_links' => $external_link_count,
						'links_per_1k' => round( $links_per_1k ),
						'url'          => get_permalink( $post->ID ),
					);
				}
			}
		}

		$excessive_percentage = ( $posts_with_excessive_links / count( $posts ) ) * 100;

		// Scoring.
		if ( $excessive_percentage < 10 ) {
			$score = 3;
		} elseif ( $excessive_percentage < 20 ) {
			$score = 2;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage, 2: number of posts */
				__( '%1$d%% of posts (%2$d) have excessive outbound links. Too many external links cause: PageRank dilution (every link "votes" for that page - too many votes = weaker each vote), Topical focus loss (search engines confused about page topic), User distraction (readers leave before finishing), Spam signals (link-heavy pages look like link schemes), Slower page load (DNS lookups for each domain). Optimal external link density: 2-5 links per 1,000 words (authority citations, sources), 1-2 affiliate/promotional links max per post, Contextual links only (relevant to topic). Exception: Resource lists/directories (curated collections intended for links). Red flags: >20 links per 1,000 words, Links to unrelated topics, Links to same domain repeatedly, Footer/sidebar link stuffing. Internal links (your own site) don\'t count toward this limit.', 'wpshadow' ),
				round( $excessive_percentage ),
				$posts_with_excessive_links
			),
			'severity'      => 'medium',
			'threat_level'  => 35,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/excessive-outbound',
			'problem_posts' => $problem_posts,
			'stats'         => array(
				'posts_checked'       => count( $posts ),
				'with_excessive'      => $posts_with_excessive_links,
				'percentage'          => round( $excessive_percentage, 1 ),
			),
			'recommendation' => __( 'Review posts with excessive links. Remove unnecessary links. Keep 2-5 authority citations per 1,000 words. Consolidate multiple links to same domain. Use rel="nofollow" on promotional links.', 'wpshadow' ),
		);
	}
}
