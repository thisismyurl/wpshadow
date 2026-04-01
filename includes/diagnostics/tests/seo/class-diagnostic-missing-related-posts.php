<?php
/**
 * Missing Related Posts Diagnostic
 *
 * Detects posts without related content links, reducing internal linking
 * and user engagement opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Engagement
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Related Posts Diagnostic Class
 *
 * Analyzes content for internal links to related posts, essential for
 * engagement, SEO, and reducing bounce rates.
 *
 * **Why This Matters:**
 * - Internal linking increases pageviews by 40%
 * - Reduces bounce rate by 15-20%
 * - Improves SEO through link equity distribution
 * - Keeps readers on your site longer
 * - Demonstrates topic authority
 *
 * **Best Practices:**
 * - 3-5 internal links per post
 * - Link to related topics naturally
 * - Use descriptive anchor text
 * - Link to both newer and older content
 *
 * @since 0.6093.1200
 */
class Diagnostic_Missing_Related_Posts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-related-posts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Related Posts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies posts without internal links to related content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if posts lack internal links, null otherwise.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$site_url = get_site_url();
		$posts_without_links = array();

		foreach ( $posts as $post ) {
			$internal_link_count = self::count_internal_links( $post->post_content, $site_url );

			if ( $internal_link_count < 2 ) {
				$posts_without_links[] = array(
					'id'                  => $post->ID,
					'title'               => $post->post_title,
					'internal_link_count' => $internal_link_count,
					'word_count'          => str_word_count( wp_strip_all_tags( $post->post_content ) ),
				);
			}
		}

		if ( empty( $posts_without_links ) ) {
			return null;
		}

		$count = count( $posts_without_links );
		$percentage = round( ( $count / count( $posts ) ) * 100 );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: percentage */
				__( '%1$d post(s) (%2$d%%) lack sufficient internal links to related content. Add 3-5 relevant internal links per post.', 'wpshadow' ),
				$count,
				$percentage
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/engagement-related-posts?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'posts_without_links' => $count,
				'percentage'          => $percentage,
				'sample_posts'        => array_slice( $posts_without_links, 0, 10 ),
			),
		);
	}

	/**
	 * Count internal links in content
	 *
	 * @since 0.6093.1200
	 * @param  string $content  Post content.
	 * @param  string $site_url Site URL for internal link detection.
	 * @return int Number of internal links found.
	 */
	private static function count_internal_links( $content, $site_url ) {
		// Extract all href attributes
		preg_match_all( '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/', $content, $matches );

		if ( empty( $matches[2] ) ) {
			return 0;
		}

		$internal_count = 0;
		foreach ( $matches[2] as $url ) {
			// Check if URL is internal (contains site URL or is relative)
			if ( strpos( $url, $site_url ) === 0 || strpos( $url, '/' ) === 0 ) {
				$internal_count++;
			}
		}

		return $internal_count;
	}
}
