<?php
/**
 * Internal Linking Strategy Diagnostic
 *
 * Tests if internal linking strategy follows SEO best practices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal Linking Strategy Diagnostic Class
 *
 * Validates that internal linking strategy follows SEO best practices
 * for link equity distribution and content discovery.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Internal_Linking_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'internal-linking-strategy';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Linking Strategy';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if internal linking strategy follows SEO best practices';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests internal linking including link density, orphaned content,
	 * and link relevance.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Get site statistics.
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_status = 'publish' AND post_type IN ('post', 'page')"
		);

		// Find orphaned content (posts with no internal links to/from).
		$orphaned_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			 WHERE p.post_status = 'publish' AND p.post_type IN ('post', 'page')
			 AND (
				 SELECT COUNT(*) FROM {$wpdb->posts} p2
				 WHERE p2.post_status = 'publish'
				 AND (p2.post_content LIKE CONCAT('%', p.guid, '%') OR p.post_content LIKE CONCAT('%', p2.guid, '%'))
			 ) = 0"
		);

		// Calculate internal link density.
		$posts_with_links = 0;
		$posts = $wpdb->get_results(
			"SELECT ID, post_content FROM {$wpdb->posts}
			 WHERE post_status = 'publish' AND post_type IN ('post', 'page')
			 ORDER BY post_date DESC LIMIT 20",
			ARRAY_A
		);

		$internal_link_count = 0;
		$external_link_count = 0;
		$site_url = get_home_url();

		foreach ( $posts as $post ) {
			preg_match_all( '/<a\s+href=[\'"](.*?)[\'"]/', $post['post_content'], $matches );
			$links = $matches[1];

			if ( ! empty( $links ) ) {
				$posts_with_links++;
			}

			foreach ( $links as $link ) {
				if ( strpos( $link, $site_url ) !== false || strpos( $link, '/' ) === 0 ) {
					$internal_link_count++;
				} else {
					$external_link_count++;
				}
			}
		}

		$posts_with_internal_links_pct = count( $posts ) > 0 ? ( $posts_with_links / count( $posts ) ) * 100 : 0;
		$link_ratio = ( $internal_link_count + $external_link_count ) > 0 ? ( $internal_link_count / ( $internal_link_count + $external_link_count ) ) * 100 : 0;

		// Check for linking strategy plugins.
		$linking_plugins = array(
			'rank-math-seo/rank-math.php'              => 'Rank Math',
			'wordpress-seo/wp-seo.php'                 => 'Yoast SEO',
			'link-whisper/link-whisper.php'            => 'Link Whisper',
			'internal-links/internal-links.php'        => 'Internal Links',
		);

		$active_linking_plugin = null;
		foreach ( $linking_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_linking_plugin = $name;
				break;
			}
		}

		// Check for related posts section.
		$has_related_posts = is_plugin_active( 'wordpress-seo/wp-seo.php' ) ||
						   is_plugin_active( 'rank-math-seo/rank-math.php' ) ||
						   is_plugin_active( 'jetpack/jetpack.php' );

		// Check for breadcrumb navigation.
		$has_breadcrumbs = is_plugin_active( 'wordpress-seo/wp-seo.php' ) ||
						 is_plugin_active( 'rank-math-seo/rank-math.php' ) ||
						 is_plugin_active( 'jetpack/jetpack.php' );

		// Check theme for navigation menus.
		$nav_menus = wp_get_nav_menus();
		$has_primary_menu = false;
		if ( function_exists( 'wp_nav_menu_exists' ) ) {
			$has_primary_menu = wp_nav_menu_exists( 'primary' ) || wp_nav_menu_exists( 'main' );
		}

		// Check for broken internal links.
		$broken_links = 0;
		foreach ( $posts as $post ) {
			preg_match_all( '/<a\s+href=[\'"](.*?)[\'"]/', $post['post_content'], $matches );
			$links = $matches[1];

			foreach ( $links as $link ) {
				if ( strpos( $link, $site_url ) === 0 || ( strpos( $link, '/' ) === 0 && strpos( $link, '//' ) !== 0 ) ) {
					// Check if link target exists.
					$post_id = url_to_postid( $link );
					if ( $post_id === 0 && strpos( $link, '?' ) === false ) {
						$broken_links++;
					}
				}
			}
		}

		// Check for issues.
		$issues = array();

		// Issue 1: Orphaned content exists.
		if ( $orphaned_posts > ( $total_posts * 0.1 ) ) {
			$issues[] = array(
				'type'        => 'orphaned_content',
				'description' => sprintf(
					/* translators: %d: number of orphaned posts */
					__( '%d posts are orphaned (no internal links); not discoverable by users or search engines', 'wpshadow' ),
					$orphaned_posts
				),
			);
		}

		// Issue 2: Few posts have internal links.
		if ( $posts_with_internal_links_pct < 50 ) {
			$issues[] = array(
				'type'        => 'low_internal_linking',
				'description' => sprintf(
					/* translators: %s: percentage */
					__( 'Only %s%% of posts link to other content; internal linking opportunity missed', 'wpshadow' ),
					round( $posts_with_internal_links_pct, 1 )
				),
			);
		}

		// Issue 3: Poor internal to external link ratio.
		if ( $link_ratio < 50 && $internal_link_count > 0 ) {
			$issues[] = array(
				'type'        => 'poor_link_ratio',
				'description' => sprintf(
					/* translators: %s: percentage */
					__( 'Only %s%% of links are internal; focus on internal linking for better SEO', 'wpshadow' ),
					round( $link_ratio, 1 )
				),
			);
		}

		// Issue 4: No linking optimization plugin.
		if ( ! $active_linking_plugin && $total_posts > 50 ) {
			$issues[] = array(
				'type'        => 'no_linking_plugin',
				'description' => __( 'No linking optimization plugin; internal linking strategy not managed', 'wpshadow' ),
			);
		}

		// Issue 5: No related posts section.
		if ( ! $has_related_posts && $total_posts > 20 ) {
			$issues[] = array(
				'type'        => 'no_related_posts',
				'description' => __( 'No related posts section; missed opportunity to improve internal linking and user engagement', 'wpshadow' ),
			);
		}

		// Issue 6: Broken internal links detected.
		if ( $broken_links > 0 ) {
			$issues[] = array(
				'type'        => 'broken_links',
				'description' => sprintf(
					/* translators: %d: number of broken links */
					__( '%d broken internal links detected; damage site authority and user experience', 'wpshadow' ),
					$broken_links
				),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Internal linking strategy is weak, reducing content discoverability and search visibility', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/internal-linking-strategy',
				'details'      => array(
					'total_posts'                => absint( $total_posts ),
					'orphaned_posts'             => absint( $orphaned_posts ),
					'posts_with_internal_links'  => absint( $posts_with_links ),
					'posts_with_internal_links_pct' => round( $posts_with_internal_links_pct, 1 ) . '%',
					'internal_link_count'        => $internal_link_count,
					'external_link_count'        => $external_link_count,
					'internal_link_ratio'        => round( $link_ratio, 1 ) . '%',
					'active_linking_plugin'      => $active_linking_plugin,
					'has_related_posts'          => $has_related_posts,
					'has_breadcrumbs'            => $has_breadcrumbs,
					'has_primary_menu'           => $has_primary_menu,
					'broken_links'               => $broken_links,
					'issues_detected'            => $issues,
					'recommendation'             => __( 'Link 50%+ of internal links, eliminate orphaned content, add related posts sections, fix broken links', 'wpshadow' ),
					'internal_linking_best_practices' => array(
						'Link Frequency'      => '1-2 internal links per 100 words',
						'Anchor Text'         => 'Use descriptive, keyword-rich anchor text',
						'Relevance'           => 'Only link to topically related content',
						'Hierarchy'           => 'Link from pillar pages to topic clusters',
						'Depth'               => 'Keep important content within 3 clicks of home',
						'No Orphans'          => 'Every page should be linked from somewhere',
					),
					'link_equity_distribution'   => 'Homepage > Pillar pages > Topic pages > Detail pages',
					'anchor_text_examples'       => array(
						'Good'  => 'Learn about WordPress security best practices',
						'Poor'  => 'Click here',
						'Exact' => 'WordPress security',
						'LSI'   => 'WordPress site protection',
					),
					'seo_impact'                 => 'Internal links pass PageRank; 3-5 internal links can increase ranking 20-40%',
					'tool_recommendations'       => array(
						'Ahrefs' => 'Site Explorer - identify orphaned pages',
						'SEMrush' => 'Site Audit - find broken links',
						'Screaming Frog' => 'Crawl site structure',
						'Yoast/Rank Math' => 'Built-in internal linking suggestions',
					),
				),
			);
		}

		return null;
	}
}
