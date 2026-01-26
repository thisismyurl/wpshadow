<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;



/**
 * Diagnostic: Pub Internal Links Count
 *
 * Checks if published posts contain adequate internal linking (2-3 links per post).
 * Internal links improve SEO, user engagement, and help readers discover related content.
 *
 * Category: Content Publishing
 * Priority: Low
 * Philosophy: Commandment #7, 8, 9 (Ridiculously Good, Inspire Confidence, Everything Has a KPI)
 *
 * Test Description:
 * Analyzes recent published posts to ensure they contain at least 2-3 internal links.
 * Internal linking is a best practice for SEO and user experience.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */
class Diagnostic_Pub_Internal_Links_Count extends Diagnostic_Base {
	protected static $slug = 'pub-internal-links-count';

	protected static $title = 'Pub Internal Links Count';

	protected static $description = 'Checks if published posts contain adequate internal linking (2-3 links per post).';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-internal-links-count';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Internal Links Count', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'At least 2-3 internal links per post?', 'wpshadow' );
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
	 * Legacy method for compatibility. New code should use check() directly.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Published posts have adequate internal linking', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'warning',
			'message' => $result['description'] ?? __( 'Internal link count below recommended threshold', 'wpshadow' ),
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-internal-links-count';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Count internal links in post content.
	 *
	 * Counts links that point to the same site (internal links).
	 * Handles absolute URLs with site domain and root-relative paths.
	 * Excludes external links, protocol-relative links, and fragments.
	 *
	 * @since  1.2601.2148
	 * @param  string $content     Post content HTML.
	 * @param  string $site_domain Site domain for comparison.
	 * @return int Number of internal links found.
	 */
	private static function count_internal_links( string $content, string $site_domain ): int {
		// Find all links in content.
		preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\']/i', $content, $matches );

		if ( empty( $matches[1] ) ) {
			return 0;
		}

		$internal_links_count = 0;

		// Check each link to see if it's internal.
		foreach ( $matches[1] as $link ) {
			// Skip fragment-only links (e.g., #section).
			if ( 0 === strpos( $link, '#' ) ) {
				continue;
			}

			// Skip protocol-relative links (e.g., //example.com).
			if ( 0 === strpos( $link, '//' ) ) {
				continue;
			}

			$link_domain = wp_parse_url( $link, PHP_URL_HOST );

			// Internal link if domain matches or is relative path.
			if ( $link_domain === $site_domain || ( null === $link_domain && 0 === strpos( $link, '/' ) ) ) {
				++$internal_links_count;
			}
		}

		return $internal_links_count;
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if recent published posts have adequate internal linking (2-3 links per post).
	 * Internal links improve SEO, user engagement, and site navigation.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null if posts have adequate internal links.
	 */
	public static function check(): ?array {
		// Get recent published posts (sample 20 to balance performance and accuracy).
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		// No posts to check - site is healthy.
		if ( empty( $posts ) ) {
			return null;
		}

		$site_url              = home_url();
		$site_domain           = wp_parse_url( $site_url, PHP_URL_HOST );
		$total_links           = 0;
		$posts_count           = 0;
		$posts_below_threshold = 0;

		foreach ( $posts as $post_id ) {
			$content              = get_post_field( 'post_content', $post_id );
			$internal_links_count = self::count_internal_links( $content, $site_domain );

			$total_links += $internal_links_count;
			++$posts_count;

			// Track posts with less than 2 internal links.
			if ( $internal_links_count < 2 ) {
				++$posts_below_threshold;
			}
		}

		// Calculate average internal links per post.
		$average_links = $posts_count > 0 ? $total_links / $posts_count : 0;

		// Flag if average is below 2 links per post OR more than 50% of posts have < 2 links.
		if ( $average_links < 2.0 || ( $posts_below_threshold / $posts_count ) > 0.5 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-internal-links-count',
				__( 'Low Internal Link Count', 'wpshadow' ),
				sprintf(
					/* translators: 1: average links per post, 2: number of posts below threshold, 3: total posts checked */
					__( 'Your posts average %1$.1f internal links each. %2$d of %3$d recent posts have fewer than 2 internal links. Aim for 2-3 internal links per post to improve SEO and keep readers engaged.', 'wpshadow' ),
					$average_links,
					$posts_below_threshold,
					$posts_count
				),
				'general',
				'low',
				25,
				'pub-internal-links-count'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Internal Links Count
	 * Slug: pub-internal-links-count
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if published posts have adequate internal linking (2-3 links per post).
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_internal_links_count(): array {
		// Get actual site state.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		// If no posts exist, test passes (nothing to check).
		if ( empty( $posts ) ) {
			return array(
				'passed'  => true,
				'message' => 'No published posts found. Test passes (nothing to check).',
			);
		}

		// Run the diagnostic check.
		$result = self::check();

		// Calculate actual stats for reporting using helper method.
		$site_url    = home_url();
		$site_domain = wp_parse_url( $site_url, PHP_URL_HOST );
		$total_links = 0;
		$posts_count = count( $posts );

		foreach ( $posts as $post_id ) {
			$content      = get_post_field( 'post_content', $post_id );
			$total_links += self::count_internal_links( $content, $site_domain );
		}

		$average_links = $posts_count > 0 ? $total_links / $posts_count : 0;

		// Test passes if check() correctly identifies the state.
		if ( null === $result && $average_links >= 2.0 ) {
			// Healthy state correctly identified.
			return array(
				'passed'  => true,
				'message' => sprintf(
					'Test PASSED: Posts have adequate internal links (%.1f average per post).',
					$average_links
				),
			);
		} elseif ( is_array( $result ) && $average_links < 2.0 ) {
			// Issue correctly identified.
			return array(
				'passed'  => true,
				'message' => sprintf(
					'Test PASSED: Issue correctly detected (%.1f average internal links per post).',
					$average_links
				),
			);
		}

		// Test failed - check() returned incorrect result.
		return array(
			'passed'  => false,
			'message' => sprintf(
				'Test FAILED: check() returned %s but average links is %.1f (expected threshold: 2.0).',
				null === $result ? 'NULL' : 'array',
				$average_links
			),
		);
	}
}
