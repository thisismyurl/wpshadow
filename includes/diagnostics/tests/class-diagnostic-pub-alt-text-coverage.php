<?php
/**
 * Diagnostic: Pub Alt Text Coverage
 *
 * Checks if published posts have adequate alt text coverage on images.
 * Alt text is essential for accessibility (screen readers) and SEO.
 *
 * This diagnostic analyzes all published posts for <img> tags and calculates
 * the percentage of images that have non-empty alt attributes. It flags
 * the site if coverage falls below 80%.
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
 * Diagnostic_Pub_Alt_Text_Coverage Class
 *
 * Verifies that images in published posts have alt text for accessibility and SEO.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Alt_Text_Coverage extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-alt-text-coverage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Alt Text Coverage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that images in published posts have alt text for accessibility and SEO.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'general';

	/**
	 * Display name for the family
	 *
	 * @var string
	 */
	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'pub-alt-text-coverage';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic display name.
	 */
	public static function get_name(): string {
		return __( 'Alt Text Coverage', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Do all images have alt text?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic category.
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @since  1.2601.2148
	 * @return int 0-100 severity level.
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Alt text coverage is adequate (>= 80%).', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string KB article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-alt-text-coverage';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Check if published posts have adequate alt text coverage on images.
	 *
	 * Analyzes all published posts for images and calculates the percentage
	 * of images that have alt text. Flags if coverage is below 80%.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if coverage is low, null if adequate.
	 */
	public static function check(): ?array {
		// Get all published posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $posts ) ) {
			return null; // No published posts to check.
		}

		$total_images       = 0;
		$images_without_alt = 0;
		$images_with_alt    = 0;

		foreach ( $posts as $post_id ) {
			$content = get_post_field( 'post_content', $post_id );

			// Find all <img> tags in post content.
			preg_match_all( '/<img\s+([^>]+)>/i', $content, $matches );

			if ( empty( $matches[1] ) ) {
				continue;
			}

			foreach ( $matches[1] as $img_attrs ) {
				++$total_images;

				// Check if alt attribute exists and is not empty.
				if ( preg_match( '/alt\s*=\s*["\']([^"\']+)["\']/', $img_attrs, $alt_match ) ) {
					$alt_text = trim( $alt_match[1] );
					if ( ! empty( $alt_text ) ) {
						++$images_with_alt;
					} else {
						++$images_without_alt;
					}
				} else {
					++$images_without_alt;
				}
			}
		}

		if ( 0 === $total_images ) {
			return null; // No images to check.
		}

		$coverage = ( $images_with_alt / $total_images ) * 100;

		// Flag if less than 80% coverage.
		if ( $coverage < 80 ) {
			$description = sprintf(
				/* translators: 1: number of images with alt text, 2: total images, 3: percentage */
				__( 'Only %1$d of %2$d images (%3$.0f%%) have alt text. Alt text is essential for accessibility and SEO. Aim for 100%% coverage.', 'wpshadow' ),
				$images_with_alt,
				$total_images,
				$coverage
			);

			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-alt-text-coverage',
				__( 'Low Alt Text Coverage', 'wpshadow' ),
				$description,
				'general',
				'low',
				30,
				'pub-alt-text-coverage'
			);
		}

		return null; // Coverage is adequate.
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Alt Text Coverage
	 * Slug: pub-alt-text-coverage
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy, coverage >= 80%)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found, coverage < 80%)
	 * - Description: Checks if published posts have adequate alt text coverage on images
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_alt_text_coverage(): array {
		$result = self::check();

		// Get actual coverage for reporting.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$total_images    = 0;
		$images_with_alt = 0;

		foreach ( $posts as $post_id ) {
			$content = get_post_field( 'post_content', $post_id );
			preg_match_all( '/<img\s+([^>]+)>/i', $content, $matches );

			if ( empty( $matches[1] ) ) {
				continue;
			}

			foreach ( $matches[1] as $img_attrs ) {
				++$total_images;

				if ( preg_match( '/alt\s*=\s*["\']([^"\']+)["\']/', $img_attrs, $alt_match ) ) {
					$alt_text = trim( $alt_match[1] );
					if ( ! empty( $alt_text ) ) {
						++$images_with_alt;
					}
				}
			}
		}

		if ( 0 === $total_images ) {
			return array(
				'passed'  => true,
				'message' => __( 'No published posts with images found. Test N/A.', 'wpshadow' ),
			);
		}

		$coverage = ( $images_with_alt / $total_images ) * 100;

		if ( null === $result && $coverage >= 80 ) {
			return array(
				'passed'  => true,
				'message' => sprintf(
					/* translators: %d: coverage percentage */
					__( 'Test passed. Alt text coverage is %.0f%% (>= 80%%).', 'wpshadow' ),
					$coverage
				),
			);
		}

		if ( null !== $result && $coverage < 80 ) {
			return array(
				'passed'  => true,
				'message' => sprintf(
					/* translators: %d: coverage percentage */
					__( 'Test passed. Correctly detected low coverage: %.0f%% (< 80%%).', 'wpshadow' ),
					$coverage
				),
			);
		}

		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: %d: coverage percentage */
				__( 'Test failed. Coverage is %.0f%% but check() returned unexpected result.', 'wpshadow' ),
				$coverage
			),
		);
	}
}
