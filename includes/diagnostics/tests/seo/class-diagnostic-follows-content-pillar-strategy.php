<?php
/**
 * Content Pillar Strategy Diagnostic
 *
 * Verifies site follows content pillar strategy with core topics and
 * supporting cluster content.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Pillar Strategy Diagnostic Class
 *
 * Analyzes content structure to detect pillar page strategy with
 * comprehensive cornerstone content and supporting cluster posts.
 *
 * **Why This Matters:**
 * - Pillar pages rank 450% better than average
 * - Topic clusters improve SEO authority
 * - Better user experience and navigation
 * - Establishes topical expertise
 * - Internal linking power compounds
 *
 * **Pillar Strategy Components:**
 * - Comprehensive pillar pages (2000+ words)
 * - Cluster content supporting pillars
 * - Internal linking between related content
 * - Clear topical hierarchy
 * - Strategic keyword targeting
 *
 * @since 1.6093.1200
 */
class Diagnostic_Follows_Content_Pillar_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'follows-content-pillar-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Pillar Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site follows pillar content strategy with comprehensive cornerstone posts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if no pillar strategy detected, null otherwise.
	 */
	public static function check() {
		$pillar_score = 0;
		$evidence = array();

		// Check 1: Long-form pillar candidates (2000+ words)
		$pillar_candidates = self::find_pillar_candidates();
		if ( count( $pillar_candidates ) >= 3 ) {
			$pillar_score += 40;
			$evidence[] = sprintf(
				/* translators: %d: number of pillar candidates */
				__( '%d long-form posts (2000+ words) suitable as pillars', 'wpshadow' ),
				count( $pillar_candidates )
			);
		}

		// Check 2: Yoast SEO cornerstone content
		$cornerstone_count = self::count_cornerstone_posts();
		if ( $cornerstone_count > 0 ) {
			$pillar_score += 30;
			$evidence[] = sprintf(
				/* translators: %d: number of cornerstone posts */
				__( '%d post(s) marked as cornerstone content', 'wpshadow' ),
				$cornerstone_count
			);
		}

		// Check 3: Strong internal linking patterns
		if ( self::has_cluster_linking() ) {
			$pillar_score += 20;
			$evidence[] = __( 'Internal linking patterns suggest cluster structure', 'wpshadow' );
		}

		// Check 4: Category structure supports pillars
		if ( self::has_pillar_structure() ) {
			$pillar_score += 10;
			$evidence[] = __( 'Category structure supports pillar organization', 'wpshadow' );
		}

		// Score >= 50 indicates pillar strategy
		if ( $pillar_score >= 50 ) {
			return null; // Pillar strategy is in use
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No content pillar strategy detected. Pillar pages rank 450% better and establish topical authority. Create comprehensive cornerstone content with supporting clusters.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/pillar-content',
			'details'      => array(
				'pillar_score'   => $pillar_score,
				'evidence_found' => $evidence,
				'recommendation' => __( 'Create 3-5 pillar pages (2000+ words) for your core topics', 'wpshadow' ),
				'strategy_steps' => array(
					'Identify your 3-5 core topics',
					'Create comprehensive pillar page for each (2000+ words)',
					'Develop 8-12 cluster posts per pillar',
					'Link cluster posts to pillar',
					'Link pillar to all related clusters',
					'Mark pillars as cornerstone in Yoast SEO',
				),
			),
		);
	}

	/**
	 * Find potential pillar page candidates
	 *
	 * @since 1.6093.1200
	 * @return array Array of posts 2000+ words.
	 */
	private static function find_pillar_candidates() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
			)
		);

		$candidates = array();
		foreach ( $posts as $post ) {
			$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
			if ( $word_count >= 2000 ) {
				$candidates[] = array(
					'id'         => $post->ID,
					'title'      => $post->post_title,
					'word_count' => $word_count,
				);
			}
		}

		return $candidates;
	}

	/**
	 * Count Yoast SEO cornerstone posts
	 *
	 * @since 1.6093.1200
	 * @return int Number of cornerstone posts.
	 */
	private static function count_cornerstone_posts() {
		global $wpdb;

		if ( ! is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			return 0;
		}

		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			WHERE meta_key = '_yoast_wpseo_is_cornerstone' 
			AND meta_value = '1'"
		);

		return (int) $count;
	}

	/**
	 * Check for cluster-style internal linking
	 *
	 * @since 1.6093.1200
	 * @return bool True if cluster linking detected.
	 */
	private static function has_cluster_linking() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$site_url = get_site_url();
		$high_link_count = 0;

		foreach ( $posts as $post ) {
			// Count internal links
			preg_match_all( '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/', $post->post_content, $matches );

			$internal_links = 0;
			if ( ! empty( $matches[2] ) ) {
				foreach ( $matches[2] as $url ) {
					if ( strpos( $url, $site_url ) === 0 || strpos( $url, '/' ) === 0 ) {
						$internal_links++;
					}
				}
			}

			// 5+ internal links suggests cluster strategy
			if ( $internal_links >= 5 ) {
				$high_link_count++;
			}
		}

		// 30%+ posts with strong internal linking
		return ( $high_link_count / count( $posts ) ) >= 0.3;
	}

	/**
	 * Check if category structure supports pillars
	 *
	 * @since 1.6093.1200
	 * @return bool True if structure is pillar-friendly.
	 */
	private static function has_pillar_structure() {
		$categories = get_categories(
			array(
				'hide_empty' => true,
				'number'     => 20,
			)
		);

		// 3-8 main categories ideal for pillar structure
		if ( count( $categories ) < 3 || count( $categories ) > 15 ) {
			return false;
		}

		// Check if categories have substantial content (10+ posts)
		$substantial_cats = 0;
		foreach ( $categories as $category ) {
			if ( $category->count >= 10 ) {
				$substantial_cats++;
			}
		}

		return $substantial_cats >= 3;
	}
}
