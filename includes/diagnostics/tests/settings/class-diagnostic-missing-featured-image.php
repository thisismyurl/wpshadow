<?php
/**
 * Diagnostic: No Featured Image
 *
 * Detects posts missing featured images, which reduce social shares by 40%
 * and hurt CTR in search results and archives.
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
 * Missing Featured Image Diagnostic Class
 *
 * Checks for featured image (post thumbnail) presence.
 *
 * Detection methods:
 * - has_post_thumbnail() check
 * - Theme support verification
 * - Missing image count
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_Featured_Image extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-featured-image';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Featured Image';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Missing featured images reduce social shares by 40%';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'structure';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 1 point: Theme supports post thumbnails
	 * - 2 points: <10% of posts missing featured image
	 * - 1 point: <30% missing
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 3;

		// Check if theme supports post thumbnails.
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			$score++;
		}

		// Get all posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$posts_without_thumbnail = 0;
		$missing_posts           = array();

		foreach ( $posts as $post ) {
			if ( ! has_post_thumbnail( $post->ID ) ) {
				$posts_without_thumbnail++;
				if ( count( $missing_posts ) < 15 ) {
					$missing_posts[] = array(
						'post_id' => $post->ID,
						'title'   => $post->post_title,
						'date'    => $post->post_date,
						'url'     => get_permalink( $post->ID ),
					);
				}
			}
		}

		$missing_percentage = ( $posts_without_thumbnail / count( $posts ) ) * 100;

		// Scoring.
		if ( $missing_percentage < 10 ) {
			$score += 2;
		} elseif ( $missing_percentage < 30 ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage, 2: number without thumbnail, 3: total posts */
				__( '%1$d%% of posts (%2$d/%3$d) lack featured images. Featured images provide: Social media previews (Facebook/Twitter cards - 40%% fewer shares without), Search result thumbnails (Google Discover, news results), Archive page visuals (blog listing pages), Email newsletter headers (RSS-to-email services), Mobile app displays (WordPress mobile apps). Impact: 40%% reduced social sharing, Lower CTR in search results (no visual = less clickable), Poor user experience on archive pages, Missed Google Discover traffic (requires high-quality images). Best practices: 1200x628px minimum (Facebook/Twitter optimized), Relevant to content (not generic stock photos), Descriptive file names (SEO benefit), Alt text added (accessibility), Compressed for speed (<200KB).', 'wpshadow' ),
				round( $missing_percentage ),
				$posts_without_thumbnail,
				count( $posts )
			),
			'severity'      => 'medium',
			'threat_level'  => 35,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/missing-featured-image',
			'missing_posts' => $missing_posts,
			'stats'         => array(
				'total_posts'      => count( $posts ),
				'without_thumbnail' => $posts_without_thumbnail,
				'percentage'       => round( $missing_percentage, 1 ),
				'theme_support'    => current_theme_supports( 'post-thumbnails' ),
			),
			'recommendation' => __( 'Add featured images to all posts. Use 1200x628px for optimal social sharing. Create templates in Canva for consistent branding. Set featured image as required field in editor.', 'wpshadow' ),
		);
	}
}
