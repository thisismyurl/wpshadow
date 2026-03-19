<?php
/**
 * Thin Content Detection Diagnostic
 *
 * Detects posts with insufficient word count that may be flagged as thin content
 * by search engines. Thin content hurts SEO and provides poor user experience.
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
 * Thin Content Detection Diagnostic Class
 *
 * Identifies published posts with inadequate word count. Google penalizes
 * thin content in rankings and may deindex pages with insufficient value.
 *
 * **Why This Matters:**
 * - Google Panda algorithm targets thin content
 * - < 300 words considered thin by most SEO standards
 * - Thin content = poor rankings, deindexing risk
 * - Competitors with comprehensive content outrank you
 *
 * **Standards:**
 * - Minimum: 300 words (absolute minimum)
 * - Recommended: 1000+ words for blog posts
 * - In-depth: 2000+ words for cornerstone content
 *
 * @since 1.6093.1200
 */
class Diagnostic_Thin_Content_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'thin-content-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Thin Content Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies posts with insufficient word count that may harm SEO rankings';

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
	 * @return array|null Finding array if thin content detected, null otherwise.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$thin_posts = array();

		foreach ( $posts as $post ) {
			$content    = wp_strip_all_tags( $post->post_content );
			$word_count = str_word_count( $content );

			if ( $word_count < 300 ) {
				$thin_posts[] = array(
					'id'         => $post->ID,
					'title'      => $post->post_title,
					'word_count' => $word_count,
					'edit_link'  => get_edit_post_link( $post->ID ),
				);
			}
		}

		if ( empty( $thin_posts ) ) {
			return null;
		}

		$count = count( $thin_posts );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of thin content posts */
				__( '%d post(s) have less than 300 words. Search engines may flag this as thin content and penalize rankings.', 'wpshadow' ),
				$count
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-thin-content',
			'details'      => array(
				'thin_count'   => $count,
				'sample_posts' => array_slice( $thin_posts, 0, 10 ),
				'recommendation' => 'Expand posts to 1000+ words or consolidate thin content',
			),
		);
	}
}
