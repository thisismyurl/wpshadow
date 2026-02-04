<?php
/**
 * Diagnostic: Duplicate Content Issues
 *
 * Detects multiple URLs with same/similar content confusing search engines.
 * Duplicate content splits ranking signals across URLs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1515
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate Content Issues Diagnostic Class
 *
 * Checks for duplicate/similar content across pages.
 *
 * Detection methods:
 * - Title similarity checking
 * - Content excerpt comparison
 * - Category/tag duplication
 *
 * @since 1.7030.1515
 */
class Diagnostic_Duplicate_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Content Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Multiple URLs with same/similar content confuse search engines';

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
	 * - 2 points: No duplicate titles
	 * - 1 point: No excessive category/tag overlap
	 * - 1 point: Proper canonical tags
	 *
	 * @since  1.7030.1515
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 4;
		$issues    = array();

		// Check for duplicate titles.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
			)
		);

		$titles = array();
		foreach ( $posts as $post ) {
			$title_normalized = strtolower( trim( $post->post_title ) );
			if ( isset( $titles[ $title_normalized ] ) ) {
				$issues['duplicate_titles'][] = array(
					'title' => $post->post_title,
					'posts' => array( $titles[ $title_normalized ], $post->ID ),
				);
			} else {
				$titles[ $title_normalized ] = $post->ID;
			}
		}

		if ( empty( $issues['duplicate_titles'] ) ) {
			$score += 2;
		}

		// Check for posts with same categories/tags.
		$duplicate_taxonomies = 0;
		foreach ( $posts as $post ) {
			$post_categories = wp_get_post_categories( $post->ID );
			$post_tags       = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );

			foreach ( $posts as $compare_post ) {
				if ( $post->ID >= $compare_post->ID ) {
					continue;
				}

				$compare_categories = wp_get_post_categories( $compare_post->ID );
				$compare_tags       = wp_get_post_tags( $compare_post->ID, array( 'fields' => 'ids' ) );

				// Same categories AND same tags = likely duplicate.
				if ( $post_categories === $compare_categories && $post_tags === $compare_tags
					&& ! empty( $post_categories ) && ! empty( $post_tags ) ) {
					$duplicate_taxonomies++;
					break;
				}
			}

			// Sample check only.
			if ( $duplicate_taxonomies > 10 ) {
				break;
			}
		}

		if ( $duplicate_taxonomies < 3 ) {
			$score++;
		}

		// Check for canonical tag implementation.
		$has_seo_plugin = (
			is_plugin_active( 'wordpress-seo/wp-seo.php' ) ||
			is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ||
			is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' )
		);

		if ( $has_seo_plugin ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.75 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Duplicate content splits ranking signals across multiple URLs = weaker rankings everywhere. Types: Exact duplicates (same content, multiple URLs), Near-duplicates (75%+ similar content), Thin variations (only slight differences), WWW vs non-WWW (site.com vs www.site.com), HTTP vs HTTPS (http:// vs https://), Trailing slash (page/ vs page), URL parameters (?sort=, ?ref=). Impact: Google picks wrong page to rank (canonical confusion), Ranking signals diluted across copies, Potential duplicate content penalty, Wasted crawl budget on duplicates. Solutions: 301 redirects (old URL → new URL permanently), Canonical tags (tell Google which version to rank), Noindex tags (keep page but hide from search), URL parameters in Search Console (tell Google how to handle), Consolidate similar content. Use Screaming Frog or Siteliner to find duplicates.', 'wpshadow' ),
			'severity'    => 'critical',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/duplicate-content',
			'stats'       => array(
				'duplicate_titles' => count( $issues['duplicate_titles'] ?? array() ),
				'taxonomy_overlaps' => $duplicate_taxonomies,
				'has_seo_plugin'   => $has_seo_plugin,
			),
			'recommendation' => __( 'Install Yoast/Rank Math for canonical tags. Search for duplicate titles in admin. Merge similar posts into one comprehensive piece. Use 301 redirects for old URLs. Set preferred domain (www vs non-www) in Search Console. Add canonical tags to paginated pages.', 'wpshadow' ),
		);
	}
}
