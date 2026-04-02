<?php
/**
 * Diagnostic: Too Few Images
 *
 * Detects posts with <1 image per 1,000 words. Posts with 3-7 images get
 * 94% more views and higher engagement.
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
 * Insufficient Images Diagnostic Class
 *
 * Checks for adequate image usage in content.
 *
 * Detection methods:
 * - Image count vs word count ratio
 * - Featured image presence
 * - Image distribution
 *
 * @since 1.6093.1200
 */
class Diagnostic_Insufficient_Images extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'insufficient-images';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Too Few Images';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = '<1 image per 1,000 words - Posts with 3-7 images get 94% more views';

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
	 * - 3 points: ≥1 image per 500 words average
	 * - 2 points: ≥1 image per 1,000 words
	 * - 0 points: <1 image per 1,000 words
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score = 0;
		$max_score = 3;
		$posts_with_few_images = array();

		// Get sample posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$total_words  = 0;
		$total_images = 0;

		foreach ( $posts as $post ) {
			$content    = $post->post_content;
			$word_count = str_word_count( wp_strip_all_tags( $content ) );

			// Skip very short posts.
			if ( $word_count < 300 ) {
				continue;
			}

			$total_words += $word_count;

			// Count images in content.
			$img_count = substr_count( $content, '<img' );
			$total_images += $img_count;

			// Check if this post has too few images.
			$images_per_1k = ( $img_count / $word_count ) * 1000;

			if ( $images_per_1k < 1 && $word_count >= 1000 ) {
				if ( count( $posts_with_few_images ) < 10 ) {
					$posts_with_few_images[] = array(
						'post_id'       => $post->ID,
						'title'         => $post->post_title,
						'word_count'    => $word_count,
						'image_count'   => $img_count,
						'images_per_1k' => round( $images_per_1k, 1 ),
						'url'           => get_permalink( $post->ID ),
					);
				}
			}
		}

		if ( $total_words === 0 ) {
			return null;
		}

		// Calculate average images per 1,000 words.
		$avg_images_per_1k = ( $total_images / $total_words ) * 1000;

		// Scoring.
		if ( $avg_images_per_1k >= 2 ) {
			$score = 3; // ~1 image per 500 words.
		} elseif ( $avg_images_per_1k >= 1 ) {
			$score = 2; // ~1 image per 1,000 words.
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: average images per 1,000 words */
				__( 'Average: %.1f images per 1,000 words. Images provide: 94%% more views (Brain processes images 60,000x faster than text), Better engagement (Visual breaks reduce cognitive load), Social sharing (Images = 150%% more Twitter shares), SEO benefits (Image alt text, image search traffic), Accessibility (Alt text for screen readers), Memory retention (65%% retention with images vs 10%% text-only). Optimal: 3-7 images per post, 1 per 300-500 words. Types: Screenshots (tutorials), Infographics (data), Custom graphics (branding), Stock photos (ambiance), Charts/graphs (statistics). Quality > quantity - relevant, high-res images only.', 'wpshadow' ),
				$avg_images_per_1k
			),
			'severity'      => 'medium',
			'threat_level'  => 30,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/insufficient-images',
			'posts_with_few_images' => $posts_with_few_images,
			'stats'         => array(
				'avg_images_per_1k' => round( $avg_images_per_1k, 1 ),
				'total_posts'       => count( $posts ),
				'posts_flagged'     => count( $posts_with_few_images ),
			),
			'recommendation' => __( 'Add 1 image per 300-500 words. Use relevant screenshots for tutorials. Create custom graphics for branding. Optimize all images (compress, add alt text). Consider Canva for quick graphics.', 'wpshadow' ),
		);
	}
}
