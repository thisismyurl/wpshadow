<?php
/**
 * Diagnostic: Pub Image Count Too Many
 *
 * Checks if published posts have too many images relative to word count.
 * Recommended ratio: 1 image per 300 words for optimal readability and performance.
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
 * Diagnostic_Pub_Image_Count_Too_Many Class
 *
 * Analyzes published posts to identify when images are overused relative
 * to text content. Too many images can distract readers and impact page
 * load performance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Image_Count_Too_Many extends Diagnostic_Base {
	/**
	 * The diagnostic slug/ID.
	 *
	 * @var string
	 */
	protected static $slug = 'pub-image-count-too-many';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Pub Image Count Too Many';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if published posts have too many images relative to word count (recommended: 1 image per 300 words).';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'general';

	/**
	 * The family label/display name.
	 *
	 * @var string
	 */
	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-image-count-too-many';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Image Count Too High', 'wpshadow' );
	}

	/**
	 * Get diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Checks if posts have too many images. Recommended: 1 image per 300 words for optimal readability.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results with status, message, and data.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Image-to-word ratios are healthy across your published posts.', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'],
			'data'    => array(
				'id'           => $result['id'],
				'title'        => $result['title'],
				'severity'     => $result['severity'],
				'threat_level' => $result['threat_level'],
				'kb_link'      => $result['kb_link'],
			),
		);
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-image-count-too-many';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if published posts have too many images relative to word count.
	 * Rule: Maximum 1 image per 300 words recommended for optimal readability.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check(): ?array {
		// Get recent published posts to analyze.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		// If no posts exist, nothing to check.
		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_too_many_images = 0;
		$affected_posts             = array();

		foreach ( $posts as $post ) {
			// Count words in post content (strip HTML tags first).
			$content    = $post->post_content;
			$plain_text = wp_strip_all_tags( $content );
			$word_count = str_word_count( $plain_text );

			// Count images in post content.
			// Handles both self-closing and regular img tags.
			preg_match_all( '/<img\s[^>]*?\/?>/i', $content, $matches );
			$image_count = count( $matches[0] );

			// Calculate maximum recommended images based on word count.
			// Rule: 1 image per 300 words.
			$max_recommended_images = ceil( $word_count / 300 );

			// If post has more images than recommended, flag it.
			if ( $image_count > $max_recommended_images && $word_count > 0 ) {
				++$posts_with_too_many_images;
				$affected_posts[] = array(
					'id'                     => $post->ID,
					'title'                  => $post->post_title,
					'word_count'             => $word_count,
					'image_count'            => $image_count,
					'max_recommended_images' => $max_recommended_images,
					'ratio'                  => $image_count > 0 ? round( $word_count / $image_count, 0 ) : 0,
				);
			}
		}

		// Calculate percentage of posts with too many images.
		$percentage = ( $posts_with_too_many_images / count( $posts ) ) * 100;

		// If more than 30% of posts have too many images, flag as issue.
		if ( $percentage > 30 ) {
			$description = sprintf(
				/* translators: 1: percentage of posts, 2: number of posts */
				__( '%1$.0f%% of your recent posts (%2$d out of %3$d) have too many images relative to word count. The recommended ratio is 1 image per 300 words for optimal readability and page load performance. Posts with too many images can distract readers and slow page load times.', 'wpshadow' ),
				$percentage,
				$posts_with_too_many_images,
				count( $posts )
			);

			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-image-count-too-many',
				__( 'Too Many Images in Posts', 'wpshadow' ),
				$description,
				'general',
				'low',
				25,
				'pub-image-count-too-many'
			);
		}

		// Less than 30% affected - site is healthy.
		return null;
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Diagnostic: Pub Image Count Too Many
	 * Slug: pub-image-count-too-many
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when posts have healthy image-to-word ratios (site is healthy)
	 * - FAIL: check() returns array when too many posts exceed the 1:300 image-to-word ratio (issue found)
	 * - Description: Checks if posts have too many images relative to word count (recommended: 1 image per 300 words).
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_pub_image_count_too_many(): array {
		// Call the diagnostic check.
		$result = self::check();

		// Get post count for context.
		$post_count = wp_count_posts( 'post' )->publish;

		if ( 0 === $post_count ) {
			// No posts exist - test passes (nothing to check).
			return array(
				'passed'  => true,
				'message' => __( 'No published posts found. Diagnostic check passed (nothing to analyze).', 'wpshadow' ),
			);
		}

		if ( null === $result ) {
			// No issues found - site is healthy.
			return array(
				'passed'  => true,
				'message' => __( 'Image-to-word ratios are healthy. Most posts maintain the recommended 1 image per 300 words or better.', 'wpshadow' ),
			);
		}

		// Issues found - diagnostic detected too many images.
		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: %s: finding description */
				__( 'Issue detected: %s', 'wpshadow' ),
				$result['description']
			),
		);
	}
}
