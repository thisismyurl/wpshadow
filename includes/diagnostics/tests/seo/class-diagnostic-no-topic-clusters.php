<?php
/**
 * Diagnostic: No Topic Clusters
 *
 * Detects missing topical authority structure (pillar pages + cluster content).
 * Topic clusters boost rankings across entire topic areas.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1514
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Topic Clusters Diagnostic Class
 *
 * Checks for pillar page strategy implementation.
 *
 * Detection methods:
 * - Pillar page identification
 * - Internal linking patterns
 * - Content organization
 *
 * @since 1.7030.1514
 */
class Diagnostic_No_Topic_Clusters extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-topic-clusters';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Topic Clusters';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Missing topical authority structure = weaker rankings across board';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Has pillar pages (3,000+ word comprehensive guides)
	 * - 1 point: Strong internal linking (avg 5+ internal links/post)
	 * - 1 point: Clear categories (10+ with descriptions)
	 *
	 * @since  1.7030.1514
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 4;

		// Check for potential pillar pages (3,000+ words).
		$long_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
			)
		);

		$pillar_candidates = 0;
		foreach ( $long_posts as $post ) {
			$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
			if ( $word_count >= 3000 ) {
				$pillar_candidates++;
			}
		}

		if ( $pillar_candidates >= 3 ) {
			$score += 2;
		}

		// Check internal linking strength.
		$total_internal_links = 0;
		$posts_checked        = 0;
		$sample_posts         = array_slice( $long_posts, 0, 20 );

		foreach ( $sample_posts as $post ) {
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );
			$internal_count = 0;

			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					if ( strpos( $url, home_url() ) !== false || strpos( $url, '/' ) === 0 ) {
						$internal_count++;
					}
				}
			}

			$total_internal_links += $internal_count;
			$posts_checked++;
		}

		$avg_internal_links = $posts_checked > 0 ? $total_internal_links / $posts_checked : 0;
		if ( $avg_internal_links >= 5 ) {
			$score++;
		}

		// Check category setup.
		$categories = get_categories( array( 'hide_empty' => true ) );
		$categories_with_descriptions = 0;

		foreach ( $categories as $category ) {
			if ( ! empty( $category->description ) ) {
				$categories_with_descriptions++;
			}
		}

		if ( count( $categories ) >= 10 && $categories_with_descriptions >= 5 ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Topic clusters (pillar-cluster model) build topical authority. Structure: Pillar page (3,000-5,000 word comprehensive guide on broad topic), Cluster content (10-20 supporting posts on specific subtopics), Internal links (all clusters link to pillar, pillar links to all clusters). Benefits: 40% rankings boost across entire topic, Establishes topical authority (Google sees you as expert), Better internal link equity distribution, Improved user experience (organized content). Example cluster: Pillar: "Complete Guide to Email Marketing" (3,500 words), Clusters: "Email Subject Lines: 15 Proven Formulas", "Email Segmentation Strategies", "Email Automation Setup Guide", "Best Email Marketing Tools". Implementation: Choose 3-5 core topics (your expertise areas), Create pillar page for each, Identify 10-15 subtopics per pillar, Create cluster content, Interlink everything. Update pillars quarterly with links to new clusters.', 'wpshadow' ),
			'severity'    => 'critical',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/no-topic-clusters',
			'stats'       => array(
				'pillar_candidates'    => $pillar_candidates,
				'avg_internal_links'   => round( $avg_internal_links, 1 ),
				'categories'           => count( $categories ),
				'with_descriptions'    => $categories_with_descriptions,
			),
			'recommendation' => __( 'Identify 3-5 core topics in your niche. Create 3,000+ word pillar page for each. List 10-15 subtopics per pillar. Write cluster content linking to pillar. Update pillar to link all clusters. Use category pages as pillars.', 'wpshadow' ),
		);
	}
}
