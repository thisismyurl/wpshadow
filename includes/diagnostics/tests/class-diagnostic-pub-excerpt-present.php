<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Excerpt Present
 *
 * Checks if published posts have custom excerpts defined. Custom excerpts
 * improve SEO, control how content appears in search results and social media,
 * and provide better user experience in archives and listings.
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7 (Beyond Pure - Free Training), 8 (Inspire Confidence), 9 (Everything Has a KPI)
 *
 * Test Description:
 * Detects when more than 50% of published posts lack custom excerpts.
 * WordPress auto-generates excerpts from content, but custom excerpts
 * provide better control and optimization opportunities.
 *
 * @since   1.2601.2148
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-26 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Pub_Excerpt_Present extends Diagnostic_Base {
	protected static $slug = 'pub-excerpt-present';

	protected static $title = 'Pub Excerpt Present';

	protected static $description = 'Automatically initialized lean diagnostic for Pub Excerpt Present. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-excerpt-present';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Excerpt Present', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Has custom excerpt (not auto-generated)?', 'wpshadow' );
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
	 * Run diagnostic test
	 *
	 * Checks if published posts have custom excerpts defined.
	 * Custom excerpts improve SEO and provide better control over
	 * how content appears in search results and social sharing.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results with status and data
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Most published posts have custom excerpts defined', 'wpshadow' ),
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
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-excerpt-present';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check
	 *
	 * Checks if more than 50% of published posts lack custom excerpts.
	 * Custom excerpts provide better SEO and control over content presentation
	 * compared to auto-generated excerpts.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues detected, null if check passes
	 */
	public static function check(): ?array {
		global $wpdb;

		// Query published posts without custom excerpts.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$posts_without_excerpt = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_status = %s 
				AND post_type = %s 
				AND (post_excerpt = '' OR post_excerpt IS NULL)",
				'publish',
				'post'
			)
		);

		// Get total published posts.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_posts = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_status = %s 
				AND post_type = %s",
				'publish',
				'post'
			)
		);

		// If no posts exist, nothing to check.
		if ( 0 === $total_posts ) {
			return null;
		}

		// Calculate percentage of posts missing excerpts.
		$percentage_missing = ( $posts_without_excerpt / $total_posts ) * 100;

		// Flag if more than 50% of posts lack custom excerpts.
		if ( $percentage_missing > 50 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-excerpt-present',
				'Posts Missing Custom Excerpts',
				sprintf(
					/* translators: 1: number of posts without excerpts, 2: total posts, 3: percentage */
					__( '%1$d of %2$d published posts (%3$.0f%%) are missing custom excerpts. Custom excerpts improve SEO, social media sharing, and provide better control over how your content appears in listings and search results.', 'wpshadow' ),
					$posts_without_excerpt,
					$total_posts,
					$percentage_missing
				),
				'general',
				'low',
				25,
				'pub-excerpt-present'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Excerpt Present
	 * Slug: pub-excerpt-present
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when more than 50% of posts have custom excerpts (site is healthy)
	 * - FAIL: check() returns array when more than 50% of posts lack custom excerpts (issue found)
	 * - Validates that published posts have custom excerpts for better SEO and content presentation
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_excerpt_present(): array {
		$result = self::check();
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Published posts have SEO-optimized excerpts configured', 'wpshadow' ),
			);
		}
		$message = $result['description'] ?? __( 'Missing excerpts on published posts detected', 'wpshadow' );
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
