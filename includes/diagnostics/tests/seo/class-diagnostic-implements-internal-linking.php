<?php
/**
 * Internal Linking Strategy Diagnostic
 *
 * Verifies site implements strategic internal linking to distribute
 * link equity and improve navigation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since      1.6034.2324
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
 * Analyzes internal linking patterns to detect strategic link placement
 * for SEO value and user navigation.
 *
 * **Why This Matters:**
 * - Internal links pass 85-90% of SEO value
 * - Strategic linking increases page views by 40%
 * - Reduces bounce rate by 15-20%
 * - Helps Google discover and index content
 * - Establishes topical relevance
 *
 * **Good Internal Linking:**
 * - 3-5 contextual links per post
 * - Descriptive anchor text
 * - Linking to related topics
 * - Both old→new and new→old links
 * - Avoiding orphan pages
 *
 * @since 1.6034.2324
 */
class Diagnostic_Implements_Internal_Linking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-internal-linking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Linking Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site implements strategic internal linking';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2324
	 * @return array|null Finding array if poor linking detected, null otherwise.
	 */
	public static function check() {
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 30,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( count( $recent_posts ) < 10 ) {
			return null; // Need sufficient content to assess
		}

		$site_url = get_site_url();
		$total_posts = count( $recent_posts );
		$posts_with_no_links = 0;
		$posts_with_few_links = 0;
		$total_internal_links = 0;
		$orphan_posts = array();

		foreach ( $recent_posts as $post ) {
			// Count internal links in content
			preg_match_all( '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/', $post->post_content, $matches );

			$internal_link_count = 0;
			if ( ! empty( $matches[2] ) ) {
				foreach ( $matches[2] as $url ) {
					if ( strpos( $url, $site_url ) === 0 || strpos( $url, '/' ) === 0 ) {
						$internal_link_count++;
					}
				}
			}

			$total_internal_links += $internal_link_count;

			if ( $internal_link_count === 0 ) {
				$posts_with_no_links++;
				$orphan_posts[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
					'links' => 0,
				);
			} elseif ( $internal_link_count < 2 ) {
				$posts_with_few_links++;
			}
		}

		$avg_links_per_post = $total_internal_links / $total_posts;
		$orphan_percentage = ( $posts_with_no_links / $total_posts ) * 100;

		// Issue if < 2 avg links per post OR > 30% orphan posts
		if ( $avg_links_per_post >= 2 && $orphan_percentage < 30 ) {
			return null; // Internal linking is adequate
		}

		$severity = 'medium';
		$threat_level = 55;

		if ( $avg_links_per_post < 1 || $orphan_percentage > 50 ) {
			$severity = 'high';
			$threat_level = 70;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: average links per post, 2: orphan percentage */
				__( 'Weak internal linking detected (avg: %1$s links/post, %2$d%% orphan posts). Strategic internal linking increases page views by 40%% and improves SEO.', 'wpshadow' ),
				number_format_i18n( $avg_links_per_post, 1 ),
				round( $orphan_percentage )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/internal-linking',
			'details'      => array(
				'avg_links_per_post'    => round( $avg_links_per_post, 1 ),
				'posts_with_no_links'   => $posts_with_no_links,
				'orphan_percentage'     => round( $orphan_percentage, 1 ),
				'sample_orphan_posts'   => array_slice( $orphan_posts, 0, 5 ),
				'recommendation'        => __( 'Add 3-5 contextual internal links to each post', 'wpshadow' ),
				'linking_best_practices' => array(
					'Link to related older posts',
					'Update older posts to link to new content',
					'Use descriptive anchor text',
					'Link naturally within content',
					'Avoid "click here" anchors',
					'Verify no orphan pages exist',
				),
			),
		);
	}
}
