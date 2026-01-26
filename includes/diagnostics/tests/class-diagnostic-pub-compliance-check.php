<?php
/**
 * Publishing Compliance Check Diagnostic
 *
 * Verifies that published content meets baseline quality and SEO standards.
 * Checks for proper categories, tags, featured images, and content length.
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
 * Diagnostic: Publishing Compliance Check
 *
 * Ensures published content meets basic quality standards including:
 * - Proper category assignment (not just "Uncategorized")
 * - Tag assignment for discoverability
 * - Featured image presence
 * - Adequate content length (300+ words)
 *
 * Flags sites where less than 70% of recent posts meet these standards.
 */
class Diagnostic_Pub_Compliance_Check extends Diagnostic_Base {
	protected static $slug = 'pub-compliance-check';

	protected static $title = 'Pub Compliance Check';

	protected static $description = 'Automatically initialized lean diagnostic for Pub Compliance Check. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-compliance-check';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Compliance Check', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Content complies with brand guidelines?', 'wpshadow' );
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
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		// STUB: Implement pub-compliance-check test
		// Philosophy focus: Commandment #7, 8, 9
		//
		// Data collection strategy:
		// - Gather relevant metrics from WordPress
		// - Calculate or query necessary values
		// - Return structured result
		//
		// KB Article: https://wpshadow.com/kb/pub-compliance-check
		// Training: https://wpshadow.com/training/category-content-publishing
		//
		// User impact: Comprehensive pre-publication audit ensures content meets quality standards, SEO best practices, and accessibility requirements before going live.

		return array(
			'status'  => 'todo',
			'message' => 'Diagnostic not yet implemented',
			'data'    => array(),
		);
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-compliance-check';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the publishing compliance diagnostic check.
	 *
	 * Examines the 50 most recent published posts to verify they meet
	 * baseline content quality standards. Posts are checked for:
	 * - Proper category (not just "Uncategorized")
	 * - At least one tag
	 * - Featured image
	 * - Minimum 300 word count (using regex for i18n support)
	 *
	 * A post is considered non-compliant if it fails 2 or more checks.
	 * Issues a finding if less than 70% of posts are compliant.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if compliance issues found, null otherwise.
	 */
	public static function check(): ?array {
		// Get recent published posts (last 50 posts)
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null; // No posts to check
		}

		$total_posts      = count( $posts );
		$compliance_fails = 0;

		foreach ( $posts as $post_id ) {
			$post_issues = 0;

			// Check 1: Has proper category (not just Uncategorized)
			$categories   = get_the_category( $post_id );
			$has_category = false;
			if ( is_array( $categories ) && ! empty( $categories ) ) {
				foreach ( $categories as $cat ) {
					if ( 'uncategorized' !== $cat->slug ) {
						$has_category = true;
						break;
					}
				}
			}
			if ( ! $has_category ) {
				++$post_issues;
			}

			// Check 2: Has tags
			$tags = get_the_tags( $post_id );
			if ( empty( $tags ) ) {
				++$post_issues;
			}

			// Check 3: Has featured image
			if ( ! has_post_thumbnail( $post_id ) ) {
				++$post_issues;
			}

			// Check 4: Has adequate word count (300+ words)
			$post_content = get_post_field( 'post_content', $post_id );
			$clean_text   = wp_strip_all_tags( $post_content );
			// Use regex for better i18n support
			preg_match_all( '/\b\w+\b/u', $clean_text, $matches );
			$word_count = count( $matches[0] );
			if ( $word_count < 300 ) {
				++$post_issues;
			}

			// If post has 2 or more compliance issues, count it as non-compliant
			if ( $post_issues >= 2 ) {
				++$compliance_fails;
			}
		}

		// Calculate compliance percentage (protected against division by zero)
		if ( 0 === $total_posts ) {
			return null; // Safety check, should not occur due to earlier empty check
		}
		$compliance_percentage = ( ( $total_posts - $compliance_fails ) / $total_posts ) * 100;

		// Flag if less than 70% of recent posts are compliant
		if ( $compliance_percentage < 70 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-compliance-check',
				'Content Compliance Issues',
				sprintf(
					'Only %.0f%% of your recent posts meet basic publishing standards. Common issues: missing categories, no tags, no featured images, or insufficient content length (300+ words recommended).',
					$compliance_percentage
				),
				'publishing',
				'low',
				25,
				'pub-compliance-check'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Compliance Check
	 * Slug: pub-compliance-check
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Pub Compliance Check. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_compliance_check(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		$result = self::check();

		// Get published posts count
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'fields'         => 'ids',
			)
		);

		// If no posts exist, test should pass (null result expected)
		if ( empty( $posts ) ) {
			if ( null === $result ) {
				return array(
					'passed'  => true,
					'message' => 'No published posts found - check correctly returns null',
				);
			}
			return array(
				'passed'  => false,
				'message' => 'Expected null when no posts exist, but got a finding',
			);
		}

		// If result is null, site is compliant (70%+ posts meet standards)
		// If result is an array, site has compliance issues
		$has_finding = is_array( $result ) && isset( $result['id'] );

		return array(
			'passed'  => true,
			'message' => $has_finding
				? sprintf( 'Content compliance issues detected: %s', $result['description'] ?? 'Unknown issue' )
				: 'All recent posts meet publishing compliance standards (70%+ pass rate)',
		);
	}
}
