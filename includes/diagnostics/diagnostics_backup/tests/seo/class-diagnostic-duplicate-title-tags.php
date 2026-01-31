<?php
/**
 * Duplicate Title Tags Diagnostic
 *
 * Detects identical title tags on multiple pages, causing SEO cannibalization
 * and confusion in search results. Unique titles improve CTR and rankings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1645
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Duplicate_Title_Tags Class
 *
 * Analyzes all published pages and posts to detect duplicate title tags.
 * Identifies exact matches and near-duplicates that could confuse search engines.
 *
 * @since 1.6028.1645
 */
class Diagnostic_Duplicate_Title_Tags extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-title-tags';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Title Tags Across Pages';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects identical title tags on multiple pages, causing SEO cannibalization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$duplicates = self::find_duplicate_titles();

		if ( empty( $duplicates ) ) {
			return null;
		}

		$total_pages      = self::count_total_pages();
		$duplicate_count  = 0;
		$examples         = array();
		$example_limit    = 5;

		foreach ( $duplicates as $title => $posts ) {
			$duplicate_count += count( $posts );
			if ( count( $examples ) < $example_limit ) {
				$examples[] = array(
					'title'      => $title,
					'post_count' => count( $posts ),
					'urls'       => array_slice( array_map( 'get_permalink', $posts ), 0, 3 ),
				);
			}
		}

		$duplicate_percentage = ( $duplicate_count / max( $total_pages, 1 ) ) * 100;

		// Determine severity
		if ( $duplicate_percentage > 10 ) {
			$severity     = 'medium';
			$threat_level = 60;
		} elseif ( $duplicate_percentage > 5 ) {
			$severity     = 'low';
			$threat_level = 40;
		} else {
			$severity     = 'info';
			$threat_level = 20;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage of duplicate titles, 2: number of duplicate sets */
				__( 'Found %1$s%% of pages with duplicate titles (%2$d duplicate sets)', 'wpshadow' ),
				number_format( $duplicate_percentage, 1 ),
				count( $duplicates )
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/duplicate-title-tags',
			'family'      => self::$family,
			'meta'        => array(
				'affected_count'     => $duplicate_count,
				'duplicate_sets'     => count( $duplicates ),
				'total_pages'        => $total_pages,
				'duplicate_percentage' => round( $duplicate_percentage, 1 ),
				'recommended'        => __( 'All pages should have unique title tags', 'wpshadow' ),
				'impact_level'       => 'medium',
				'immediate_actions'  => array(
					__( 'Review duplicate title examples', 'wpshadow' ),
					__( 'Add unique identifiers to titles', 'wpshadow' ),
					__( 'Configure SEO plugin title templates', 'wpshadow' ),
					__( 'Test search appearance', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Duplicate title tags confuse search engines about which page to rank for queries, dilute ranking power across multiple pages, reduce click-through rates, and create a poor user experience in search results.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Search Cannibalization: Multiple pages compete for the same queries', 'wpshadow' ),
					__( 'Ranking Dilution: Authority split across duplicate pages', 'wpshadow' ),
					__( 'Poor CTR: Users can\'t distinguish pages in search results', 'wpshadow' ),
					__( 'Wasted Crawl Budget: Search engines waste time on duplicates', 'wpshadow' ),
				),
				'examples'      => $examples,
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Manual Title Optimization', 'wpshadow' ),
						'description' => __( 'Review and update duplicate titles manually', 'wpshadow' ),
						'steps'       => array(
							__( 'Review list of duplicate title tags', 'wpshadow' ),
							__( 'Add unique identifiers (location, date, category)', 'wpshadow' ),
							__( 'Edit each page to add distinguishing information', 'wpshadow' ),
							__( 'Verify uniqueness in SEO plugin', 'wpshadow' ),
							__( 'Monitor search console for improvements', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'SEO Plugin Title Templates', 'wpshadow' ),
						'description' => __( 'Use Yoast or RankMath to auto-generate unique titles', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Yoast SEO (free) or RankMath (free)', 'wpshadow' ),
							__( 'Configure title templates: %%title%% %%sep%% %%sitename%%', 'wpshadow' ),
							__( 'Add unique variables: %%category%%, %%date%%, %%page%%', 'wpshadow' ),
							__( 'Bulk regenerate titles for existing content', 'wpshadow' ),
							__( 'Test with SEO plugin title preview', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Database Bulk Update Script', 'wpshadow' ),
						'description' => __( 'Write custom SQL to batch-update duplicate titles', 'wpshadow' ),
						'steps'       => array(
							__( 'Export duplicate titles to CSV', 'wpshadow' ),
							__( 'Create unique title patterns', 'wpshadow' ),
							__( 'Write WP-CLI script for bulk updates', 'wpshadow' ),
							__( 'Test on staging environment first', 'wpshadow' ),
							__( 'Deploy to production with backup', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Include primary keyword near beginning of title', 'wpshadow' ),
					__( 'Add branding (site name) consistently', 'wpshadow' ),
					__( 'Use unique identifiers for similar content', 'wpshadow' ),
					__( 'Keep titles 50-60 characters for SERP display', 'wpshadow' ),
					__( 'Avoid keyword stuffing or over-optimization', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Run this diagnostic after making changes', 'wpshadow' ),
						__( 'Check Google Search Console for improvements', 'wpshadow' ),
						__( 'Search for "site:yourdomain.com" to view titles', 'wpshadow' ),
						__( 'Monitor CTR changes in Search Console', 'wpshadow' ),
					),
					'expected_result' => __( 'All pages have unique, descriptive title tags', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Find duplicate title tags across all published content.
	 *
	 * @since  1.6028.1645
	 * @return array Associative array of duplicate titles and their post IDs.
	 */
	private static function find_duplicate_titles() {
		$titles = array();

		// Get all published posts and pages.
		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return array();
		}

		// Collect titles with their post IDs.
		foreach ( $posts as $post_id ) {
			$title = self::get_title_for_post( $post_id );
			if ( ! empty( $title ) ) {
				if ( ! isset( $titles[ $title ] ) ) {
					$titles[ $title ] = array();
				}
				$titles[ $title ][] = $post_id;
			}
		}

		// Filter to only duplicates (2+ posts with same title).
		$duplicates = array_filter( $titles, function( $posts ) {
			return count( $posts ) > 1;
		});

		return $duplicates;
	}

	/**
	 * Get the SEO title for a specific post.
	 *
	 * Checks SEO plugin meta first, falls back to post title.
	 *
	 * @since  1.6028.1645
	 * @param  int $post_id Post ID.
	 * @return string Title tag value.
	 */
	private static function get_title_for_post( $post_id ) {
		// Check Yoast SEO.
		$yoast_title = get_post_meta( $post_id, '_yoast_wpseo_title', true );
		if ( ! empty( $yoast_title ) ) {
			return $yoast_title;
		}

		// Check RankMath.
		$rankmath_title = get_post_meta( $post_id, 'rank_math_title', true );
		if ( ! empty( $rankmath_title ) ) {
			return $rankmath_title;
		}

		// Fallback to WordPress title.
		$post = get_post( $post_id );
		return $post ? get_the_title( $post ) : '';
	}

	/**
	 * Count total published pages and posts.
	 *
	 * @since  1.6028.1645
	 * @return int Total page count.
	 */
	private static function count_total_pages() {
		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$posts = get_posts( $args );
		return count( $posts );
	}
}
