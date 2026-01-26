<?php
/**
 * Diagnostic: Published Posts Heading Count
 *
 * Checks if published posts have sufficient heading tags (H2-H6) for proper
 * content structure, readability, and SEO.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Pub_Heading_Count Class
 *
 * Verifies that published posts contain adequate subheading structure.
 * Posts should have at least 3-5 subheadings (H2-H6) to improve:
 * - Content scannability and readability
 * - SEO through better content structure
 * - Accessibility for screen readers
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Heading_Count extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-heading-count';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Posts Have Sufficient Headings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that published posts have adequate heading structure (3-5+ subheadings) for readability and SEO.';

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
	 * Get diagnostic ID.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'pub-heading-count';
	}

	/**
	 * Get diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Heading Count', 'wpshadow' );
	}

	/**
	 * Get diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Brief description of what this diagnostic checks.
	 */
	public static function get_description(): string {
		return __( 'At least 3-5 subheadings?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category.
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level.
	 *
	 * @since  1.2601.2148
	 * @return int Severity level (0-100).
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Posts have sufficient heading structure', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'warning',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL.
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-heading-count';
	}

	/**
	 * Get training video URL.
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if published posts have sufficient heading tags (H2-H6) for proper content structure.
	 * Posts should have at least 3-5 subheadings to improve readability, SEO, and accessibility.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check(): ?array {
		// Get recent published posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		// No posts to check.
		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_few_headings = 0;
		$min_headings            = 3;

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Count heading tags (H2 through H6, excluding H1 which should be post title).
			$heading_count  = 0;
			$heading_count += preg_match_all( '/<h2[^>]*>/i', $content );
			$heading_count += preg_match_all( '/<h3[^>]*>/i', $content );
			$heading_count += preg_match_all( '/<h4[^>]*>/i', $content );
			$heading_count += preg_match_all( '/<h5[^>]*>/i', $content );
			$heading_count += preg_match_all( '/<h6[^>]*>/i', $content );

			// Flag if post has fewer than minimum required headings.
			if ( $heading_count < $min_headings ) {
				++$posts_with_few_headings;
			}
		}

		// Calculate percentage of posts lacking sufficient headings.
		$percentage = ( $posts_with_few_headings / count( $posts ) ) * 100;

		// If more than 30% of posts lack sufficient headings, flag as issue.
		if ( $percentage > 30 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-heading-count',
				__( 'Posts Lack Sufficient Headings', 'wpshadow' ),
				sprintf(
					/* translators: 1: percentage of posts, 2: minimum number of headings */
					__( '%1$.0f%% of recent posts have fewer than %2$d subheadings. Using H2-H6 tags improves content structure, readability, and SEO.', 'wpshadow' ),
					$percentage,
					$min_headings
				),
				'general',
				'low',
				25,
				'pub-heading-count'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Verifies that the check() method returns the correct result based on
	 * the current site's published posts heading structure.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result information.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_pub_heading_count(): array {
		$result = self::check();

		// If check() returns null, the site is healthy (posts have sufficient headings).
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Test passed: Published posts have sufficient heading structure', 'wpshadow' ),
			);
		}

		// If check() returns an array, there's an issue (posts lack sufficient headings).
		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: %s: diagnostic description */
				__( 'Test failed: %s', 'wpshadow' ),
				$result['description']
			),
		);
	}
}
