<?php
/**
 * Pagination Markup Diagnostic
 *
 * Detects missing rel="prev" and rel="next" pagination markup for multi-page
 * content series. Proper markup helps Google understand content relationships.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Pagination_Markup_Missing Class
 *
 * Checks for proper rel="prev" and rel="next" pagination links on
 * multi-page archives, search results, and paginated post content.
 *
 * @since 1.6028.1700
 */
class Diagnostic_Pagination_Markup_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pagination-markup-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pagination Markup Missing rel="prev"/"next"';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing pagination markup for multi-page content series';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1700
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site has paginated content.
		$has_pagination = self::has_paginated_content();

		if ( ! $has_pagination ) {
			return null; // No pagination, so markup not needed.
		}

		// Check if pagination markup is implemented.
		$markup_status = self::check_pagination_markup();

		if ( $markup_status['implemented'] ) {
			return null; // Pagination markup is present.
		}

		// Determine severity based on pagination volume.
		$paginated_archives = $markup_status['paginated_archives'];
		if ( $paginated_archives > 20 ) {
			$severity     = 'low';
			$threat_level = 35;
		} elseif ( $paginated_archives > 5 ) {
			$severity     = 'info';
			$threat_level = 25;
		} else {
			$severity     = 'info';
			$threat_level = 15;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of paginated archives */
				__( 'Site has %d paginated archives but no rel="prev"/"next" markup detected', 'wpshadow' ),
				$paginated_archives
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/pagination-markup',
			'family'      => self::$family,
			'meta'        => array(
				'affected_count'      => $paginated_archives,
				'pagination_type'     => $markup_status['pagination_type'],
				'seo_plugin_active'   => $markup_status['seo_plugin_active'],
				'recommended'         => __( 'Add rel="prev"/"next" or use view-all canonical', 'wpshadow' ),
				'impact_level'        => 'low',
				'immediate_actions'   => array(
					__( 'Install SEO plugin (Yoast/RankMath) for automatic markup', 'wpshadow' ),
					__( 'Verify markup on page 2+ of archives', 'wpshadow' ),
					__( 'Test with Google Search Console', 'wpshadow' ),
					__( 'Consider view-all page alternative', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Pagination markup (rel="prev" and rel="next") helps search engines understand the relationship between pages in a series. Without it, Google may treat each page as independent, causing duplicate content issues or inefficient crawling. However, Google deprecated official support in 2019, so canonical tags are now preferred.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Duplicate Content Risk: Pages compete instead of being seen as series', 'wpshadow' ),
					__( 'Crawl Inefficiency: Search engines waste time on pagination', 'wpshadow' ),
					__( 'Ranking Confusion: Authority diluted across paginated pages', 'wpshadow' ),
					__( 'Minor Impact: Google deprecated in 2019, now uses heuristics', 'wpshadow' ),
				),
				'current_state' => array(
					'paginated_archives' => $paginated_archives,
					'pagination_type'    => $markup_status['pagination_type'],
					'seo_plugin'         => $markup_status['seo_plugin_active'],
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'SEO Plugin Automatic Markup', 'wpshadow' ),
						'description' => __( 'Install Yoast SEO or RankMath for automatic pagination markup', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Yoast SEO (free) or RankMath (free)', 'wpshadow' ),
							__( 'Plugin auto-adds rel="prev"/"next" to paginated pages', 'wpshadow' ),
							__( 'Navigate to page 2 of blog archive', 'wpshadow' ),
							__( 'View source and verify <link rel="prev"> and <link rel="next">', 'wpshadow' ),
							__( 'Test with Google Rich Results Test', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Manual Theme Implementation', 'wpshadow' ),
						'description' => __( 'Add pagination links to theme header.php', 'wpshadow' ),
						'steps'       => array(
							__( 'Open theme header.php file', 'wpshadow' ),
							__( 'Add before </head>: <?php wp_head(); ?>', 'wpshadow' ),
							__( 'WordPress outputs pagination via wp_head hook (if theme supports)', 'wpshadow' ),
							__( 'Or manually add using get_previous_posts_link() and get_next_posts_link()', 'wpshadow' ),
							__( 'Test on multi-page archives', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'View-All Page Alternative', 'wpshadow' ),
						'description' => __( 'Use canonical to point paginated pages to view-all URL', 'wpshadow' ),
						'steps'       => array(
							__( 'Create view-all page showing all content', 'wpshadow' ),
							__( 'Set canonical on page 2+ to view-all URL', 'wpshadow' ),
							__( 'Use rel="canonical" href="https://example.com/category/all/"', 'wpshadow' ),
							__( 'Ensure view-all page is crawlable and indexable', 'wpshadow' ),
							__( 'Monitor Search Console for indexation changes', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Canonical tags now preferred over rel="prev"/"next" (post-2019)', 'wpshadow' ),
					__( 'Point page 2+ canonical to page 1 for consolidation', 'wpshadow' ),
					__( 'Or use self-referential canonical if pages are unique', 'wpshadow' ),
					__( 'Consider infinite scroll with dynamic canonical updates', 'wpshadow' ),
					__( 'Monitor Search Console for crawl/indexation issues', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Navigate to page 2 of blog or category archive', 'wpshadow' ),
						__( 'View page source and search for rel="prev" and rel="next"', 'wpshadow' ),
						__( 'Verify prev points to page 1, next points to page 3', 'wpshadow' ),
						__( 'Check Search Console for duplicate content warnings', 'wpshadow' ),
					),
					'expected_result' => __( 'Page 2+ has <link rel="prev"> and <link rel="next"> in <head>', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Check if site has paginated content.
	 *
	 * @since  1.6028.1700
	 * @return bool True if pagination exists.
	 */
	private static function has_paginated_content() {
		global $wpdb;

		// Check if blog has enough posts to paginate.
		$posts_per_page = get_option( 'posts_per_page', 10 );
		$total_posts    = wp_count_posts( 'post' )->publish;

		if ( $total_posts > $posts_per_page ) {
			return true;
		}

		// Check if any category has enough posts to paginate.
		$categories = get_categories( array( 'hide_empty' => true ) );
		foreach ( $categories as $category ) {
			if ( $category->count > $posts_per_page ) {
				return true;
			}
		}

		// Check if comments are paginated.
		$comments_per_page = get_option( 'comments_per_page', 50 );
		$max_comments      = $wpdb->get_var( "SELECT MAX(comment_count) FROM {$wpdb->posts} WHERE post_status = 'publish'" );
		if ( $max_comments > $comments_per_page ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if pagination markup is implemented.
	 *
	 * @since  1.6028.1700
	 * @return array Status of pagination markup.
	 */
	private static function check_pagination_markup() {
		$result = array(
			'implemented'        => false,
			'paginated_archives' => 0,
			'pagination_type'    => 'none',
			'seo_plugin_active'  => false,
		);

		// Check if SEO plugin is active (they handle pagination markup).
		if ( function_exists( 'wpseo_auto_load' ) ) {
			$result['implemented']       = true;
			$result['pagination_type']   = 'yoast_seo';
			$result['seo_plugin_active'] = true;
			return $result;
		}

		if ( class_exists( 'RankMath' ) ) {
			$result['implemented']       = true;
			$result['pagination_type']   = 'rankmath';
			$result['seo_plugin_active'] = true;
			return $result;
		}

		// Check if theme adds pagination markup via wp_head.
		if ( has_action( 'wp_head', 'rel_canonical' ) || has_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' ) ) {
			$result['implemented']     = true;
			$result['pagination_type'] = 'theme';
			return $result;
		}

		// Calculate number of paginated archives.
		$posts_per_page          = get_option( 'posts_per_page', 10 );
		$total_posts             = wp_count_posts( 'post' )->publish;
		$result['paginated_archives'] = ceil( $total_posts / $posts_per_page ) - 1;

		return $result;
	}
}
