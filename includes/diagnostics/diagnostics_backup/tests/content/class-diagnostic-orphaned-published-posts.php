<?php
/**
 * Orphaned Published Posts Diagnostic
 *
 * Identifies published posts not linked from anywhere (no categories, tags, menus, or internal links).
 * Orphaned content wastes content creation investment and provides no SEO value.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Orphaned_Published_Posts Class
 *
 * Detects published posts/pages that are not discoverable through site navigation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Orphaned_Published_Posts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-published-posts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Published Posts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies published posts not linked from anywhere on the site';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$orphan_data = self::find_orphaned_posts();

		if ( ! $orphan_data ) {
			return null;
		}

		$orphan_count      = $orphan_data['orphan_count'];
		$total_posts       = $orphan_data['total_posts'];
		$orphan_percentage = $orphan_data['orphan_percentage'];
		$sample_posts      = $orphan_data['sample_posts'];

		// Thresholds: <5% good, 5-10% warning, >10% critical.
		if ( $orphan_percentage < 5 ) {
			return null; // Acceptable orphan rate.
		}

		$severity     = 'medium';
		$threat_level = 55;

		if ( $orphan_percentage > 10 ) {
			$severity     = 'high';
			$threat_level = 70;
		} elseif ( $orphan_percentage > 15 ) {
			$severity     = 'critical';
			$threat_level = 80;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: Orphan count, 2: Orphan percentage, 3: Total posts */
				__( '%1$d published posts (%2$d%% of %3$d total) are orphaned - not linked from categories, tags, menus, or other content. This wastes content investment and provides no SEO value.', 'wpshadow' ),
				$orphan_count,
				$orphan_percentage,
				$total_posts
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/orphaned-posts',
			'details'     => self::get_details( $orphan_data ),
		);
	}

	/**
	 * Find orphaned published posts.
	 *
	 * Checks for posts without categories, tags, or menu links.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Orphaned post data.
	 *
	 *     @type int   $orphan_count      Number of orphaned posts.
	 *     @type int   $total_posts       Total published posts.
	 *     @type int   $orphan_percentage Percentage orphaned.
	 *     @type array $sample_posts      Sample orphaned post titles.
	 * }
	 */
	private static function find_orphaned_posts() {
		global $wpdb;

		// Get all published posts/pages.
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page')"
		);

		if ( ! $total_posts || $total_posts < 10 ) {
			return null; // Too few posts to analyze.
		}

		// Find posts without categories or tags (uncategorized doesn't count).
		$posts_without_terms = $wpdb->get_results(
			"SELECT p.ID, p.post_title 
			FROM {$wpdb->posts} p
			WHERE p.post_status = 'publish' 
			AND p.post_type = 'post'
			AND NOT EXISTS (
				SELECT 1 FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tr.object_id = p.ID 
				AND tt.taxonomy IN ('category', 'post_tag')
				AND tt.term_id != 1
			)",
			ARRAY_A
		);

		// Find pages not in navigation menus.
		$pages_not_in_menus = $wpdb->get_results(
			"SELECT p.ID, p.post_title 
			FROM {$wpdb->posts} p
			WHERE p.post_status = 'publish' 
			AND p.post_type = 'page'
			AND NOT EXISTS (
				SELECT 1 FROM {$wpdb->postmeta} pm
				WHERE pm.post_id = p.ID 
				AND pm.meta_key = '_menu_item_object_id'
			)",
			ARRAY_A
		);

		// Combine orphaned posts.
		$orphaned_posts = array_merge( $posts_without_terms, $pages_not_in_menus );
		$orphan_count   = count( $orphaned_posts );

		if ( $orphan_count === 0 ) {
			return null;
		}

		$orphan_percentage = round( ( $orphan_count / $total_posts ) * 100 );

		// Get sample post titles (first 5).
		$sample_posts = array_slice(
			array_column( $orphaned_posts, 'post_title' ),
			0,
			5
		);

		return array(
			'orphan_count'      => $orphan_count,
			'total_posts'       => $total_posts,
			'orphan_percentage' => $orphan_percentage,
			'sample_posts'      => $sample_posts,
		);
	}

	/**
	 * Get detailed information about the finding.
	 *
	 * @since  1.2601.2148
	 * @param  array $orphan_data Orphaned post data.
	 * @return array Details array with explanation and solutions.
	 */
	private static function get_details( $orphan_data ) {
		$orphan_count      = $orphan_data['orphan_count'];
		$total_posts       = $orphan_data['total_posts'];
		$orphan_percentage = $orphan_data['orphan_percentage'];
		$sample_posts      = $orphan_data['sample_posts'];

		$sample_list = '';
		if ( ! empty( $sample_posts ) ) {
			$sample_list = implode( ', ', array_map( 'esc_html', $sample_posts ) );
		}

		$explanation = sprintf(
			/* translators: 1: Orphan count, 2: Percentage, 3: Total posts, 4: Sample titles */
			__( '%1$d of %3$d published posts (%2$d%%) are orphaned - not discoverable through categories, tags, menus, or internal links. Examples: %4$s. Orphaned content wastes the time and money invested in content creation, provides no SEO value (Google can\'t find it), and represents lost conversion opportunities.', 'wpshadow' ),
			$orphan_count,
			$orphan_percentage,
			$total_posts,
			$sample_list ? $sample_list : __( 'No samples available', 'wpshadow' )
		);

		$solutions = array(
			'free' => array(
				__( 'Add categories/tags: Assign posts to relevant taxonomies so they appear in archives', 'wpshadow' ),
				__( 'Link from homepage: Feature important content on the homepage', 'wpshadow' ),
				__( 'Internal linking: Add links from related posts', 'wpshadow' ),
				__( 'Add to navigation menu: Include pages in site navigation', 'wpshadow' ),
			),
			'premium' => array(
				__( 'Related posts plugin: Automatically link similar content (YARPP, Related Posts)', 'wpshadow' ),
				__( 'XML sitemap: Ensure all content is in sitemap for search engines', 'wpshadow' ),
				__( 'Content audit: Review and consolidate or delete orphaned content', 'wpshadow' ),
			),
			'advanced' => array(
				__( 'Content hub structure: Organize content into pillar pages and clusters', 'wpshadow' ),
				__( 'Automated interlinking: Use AI to suggest relevant internal links', 'wpshadow' ),
				__( 'Archive restructure: Create custom archive pages for better content discovery', 'wpshadow' ),
			),
		);

		$additional_info = sprintf(
			/* translators: 1: Recommended percentage */
			__( 'Recommended: <5%% of content should be orphaned. Orphaned content receives no traffic, provides no SEO value, and wastes content creation investment. Every piece of content should be discoverable through at least 2-3 paths.', 'wpshadow' )
		);

		return array(
			'explanation'     => $explanation,
			'solutions'       => $solutions,
			'additional_info' => $additional_info,
			'technical_data'  => array(
				'orphan_count'      => $orphan_count,
				'total_posts'       => $total_posts,
				'orphan_percentage' => $orphan_percentage . '%',
				'sample_posts'      => $sample_posts,
				'threshold_warning' => '5-10%',
				'threshold_critical' => '>10%',
			),
			'resources'       => array(
				array(
					'label' => __( 'Internal Linking Strategy', 'wpshadow' ),
					'url'   => 'https://yoast.com/internal-linking-for-seo-why-and-how/',
				),
				array(
					'label' => __( 'Content Pillar Strategy', 'wpshadow' ),
					'url'   => 'https://blog.hubspot.com/marketing/content-cluster-pillar',
				),
			),
		);
	}
}
