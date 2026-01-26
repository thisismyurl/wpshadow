<?php
/**
 * Diagnostic: Posts Have Featured Images
 *
 * Checks if published posts have featured images. Featured images improve
 * social media sharing (Open Graph, Twitter Cards), SEO, user engagement,
 * and site aesthetics.
 *
 * Category: Content Publishing
 * Priority: Low
 * Philosophy: Commandment #7 (Ridiculously Good for Free), #8 (Inspire Confidence)
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic_Pub_Featured_Image_Present Class
 *
 * Detects when published posts are missing featured images.
 * Checks recent posts (last 6 months) and flags if less than 70%
 * have featured images set.
 *
 * Featured images are critical for:
 * - Social media sharing (Open Graph, Twitter Cards)
 * - SEO and visual content optimization
 * - User engagement and click-through rates
 * - Professional site appearance
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Featured_Image_Present extends Diagnostic_Base {
	protected static $slug = 'pub-featured-image-present';

	protected static $title = 'Posts Have Featured Images';

	protected static $description = 'Checks if published posts have featured images set. Featured images improve social media sharing, SEO, and user engagement.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier
	 */
	public static function get_id(): string {
		return 'pub-featured-image-present';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Featured Image Present', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Checks if published posts have featured images. Featured images improve social sharing and engagement.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * Low severity as this is a content quality issue, not a security
	 * or critical performance concern.
	 *
	 * @since  1.2601.2148
	 * @return int 0-100 severity level (25 = low)
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test
	 *
	 * Legacy method that wraps check() for backward compatibility.
	 * Modern code should call check() directly.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		$finding = self::check();

		if ( null === $finding ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Most of your posts have featured images', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $finding['description'],
			'data'    => $finding,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-featured-image-present';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check
	 *
	 * Checks if published posts have featured images set. Featured images
	 * are important for social media sharing, SEO, and user engagement.
	 *
	 * @since  1.2601.2148
	 * @return array|null Null if pass (>70% coverage), array of findings if fail
	 */
	public static function check(): ?array {
		// Get recent published posts from the last 6 months.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'date_query'     => array(
					array(
						'after'  => '6 months ago',
						'column' => 'post_date',
					),
				),
			)
		);

		// If no recent posts, consider it passing (nothing to check).
		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_featured = 0;
		foreach ( $posts as $post_id ) {
			if ( has_post_thumbnail( $post_id ) ) {
				++$posts_with_featured;
			}
		}

		$total_posts = count( $posts );
		$percentage  = ( $posts_with_featured / $total_posts ) * 100;

		// If less than 70% of posts have featured images, flag as an issue.
		if ( $percentage < 70 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-featured-image-present',
				__( 'Posts Missing Featured Images', 'wpshadow' ),
				sprintf(
					/* translators: 1: percentage of posts with featured images, 2: number of posts without featured images, 3: total number of posts */
					__( 'Only %1$.0f%% (%2$d of %3$d) of your recent posts have featured images. Featured images improve social media sharing, SEO, and user engagement. Consider adding featured images to your posts.', 'wpshadow' ),
					$percentage,
					$posts_with_featured,
					$total_posts
				),
				'general',
				'low',
				25,
				'pub-featured-image-present'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Validates that the check() method returns the correct result based
	 * on the current site state.
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result array.
	 *
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_featured_image_present(): array {
		$result = self::check();

		// Get post count to provide meaningful test feedback.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'date_query'     => array(
					array(
						'after'  => '6 months ago',
						'column' => 'post_date',
					),
				),
			)
		);

		$total_posts = count( $posts );

		// If no posts, the check should pass.
		if ( 0 === $total_posts ) {
			if ( null === $result ) {
				return array(
					'passed'  => true,
					'message' => __( 'Test passed: No recent posts found, diagnostic correctly returned null', 'wpshadow' ),
				);
			}

			return array(
				'passed'  => false,
				'message' => __( 'Test failed: No recent posts found, but diagnostic returned a finding', 'wpshadow' ),
			);
		}

		// Count posts with featured images.
		$posts_with_featured = 0;
		foreach ( $posts as $post_id ) {
			if ( has_post_thumbnail( $post_id ) ) {
				++$posts_with_featured;
			}
		}

		$percentage = ( $posts_with_featured / $total_posts ) * 100;

		// Verify the check() logic matches expectations.
		if ( $percentage >= 70 ) {
			if ( null === $result ) {
				return array(
					'passed'  => true,
					'message' => sprintf(
						/* translators: 1: percentage, 2: count with featured, 3: total count */
						__( 'Test passed: %1$.0f%% (%2$d of %3$d) posts have featured images (above 70%% threshold), diagnostic correctly returned null', 'wpshadow' ),
						$percentage,
						$posts_with_featured,
						$total_posts
					),
				);
			}

			return array(
				'passed'  => false,
				'message' => sprintf(
					/* translators: 1: percentage, 2: count with featured, 3: total count */
					__( 'Test failed: %1$.0f%% (%2$d of %3$d) posts have featured images (above 70%% threshold), but diagnostic returned a finding', 'wpshadow' ),
					$percentage,
					$posts_with_featured,
					$total_posts
				),
			);
		} else {
			if ( null !== $result && is_array( $result ) ) {
				return array(
					'passed'  => true,
					'message' => sprintf(
						/* translators: 1: percentage, 2: count with featured, 3: total count */
						__( 'Test passed: %1$.0f%% (%2$d of %3$d) posts have featured images (below 70%% threshold), diagnostic correctly returned a finding', 'wpshadow' ),
						$percentage,
						$posts_with_featured,
						$total_posts
					),
				);
			}

			return array(
				'passed'  => false,
				'message' => sprintf(
					/* translators: 1: percentage, 2: count with featured, 3: total count */
					__( 'Test failed: %1$.0f%% (%2$d of %3$d) posts have featured images (below 70%% threshold), but diagnostic returned null', 'wpshadow' ),
					$percentage,
					$posts_with_featured,
					$total_posts
				),
			);
		}
	}
}
