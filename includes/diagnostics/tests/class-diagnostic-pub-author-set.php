<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Author Set Correctly
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7, 8, 9
 *
 * Test Description:
 * Verifies that published posts have valid authors set and that those
 * authors have complete biographical information. This improves content
 * credibility and reader trust.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2148
 *
 * @verified 2026-01-26 - Fully implemented with author and bio validation
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Pub_Author_Set extends Diagnostic_Base {
	protected static $slug = 'pub-author-set';

	protected static $title = 'Pub Author Set';

	protected static $description = 'Automatically initialized lean diagnostic for Pub Author Set. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string The diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'pub-author-set';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string The diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Author Set Correctly', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string The diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Post author set and bio filled?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string The diagnostic category.
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @since  1.2601.2148
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test
	 *
	 * Checks if published posts have valid authors set and if those
	 * authors have complete biographical information.
	 *
	 * @since 1.2601.2148
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		// Check posts for author attribution and bio completion.
		// Philosophy focus: Commandment #7 (Drive to Knowledge Base),
		// #8 (Inspire Confidence), #9 (Everything Has a KPI)
		//
		// Data collection strategy:
		// - Query all published posts
		// - Verify each post has a valid author
		// - Check if author has biographical information
		// - Calculate percentage of posts with complete author data
		//
		// KB Article: https://wpshadow.com/kb/pub-author-set
		// Training: https://wpshadow.com/training/category-content-publishing
		//
		// User impact: Ensures content has proper attribution and author credibility,
		// improving reader trust and meeting content quality standards.

		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Published posts have proper author attribution and complete author bios', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => isset( $result['description'] ) ? $result['description'] : __( 'Author attribution issue detected', 'wpshadow' ),
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string The knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-author-set';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string The training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if published posts have valid authors with complete biographical
	 * information. Returns a finding if less than 70% of posts meet this criteria.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
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
			return null; // No posts to check.
		}

		$posts_with_author_and_bio = 0;
		$posts_with_valid_author   = 0;

		foreach ( $posts as $post_id ) {
			$post = get_post( $post_id );

			// Check if author is set and valid.
			if ( empty( $post->post_author ) || 0 === absint( $post->post_author ) ) {
				continue; // No author or invalid author.
			}

			$author = get_user_by( 'id', $post->post_author );

			// Verify author exists.
			if ( ! $author ) {
				continue; // Orphaned author reference.
			}

			++$posts_with_valid_author;

			// Check if author has a bio.
			$bio = get_user_meta( $post->post_author, 'description', true );
			if ( ! empty( $bio ) ) {
				++$posts_with_author_and_bio;
			}
		}

		// Calculate percentage of posts with author AND bio.
		if ( 0 === $posts_with_valid_author ) {
			// No valid authors at all.
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-author-set',
				'Post Authors Missing',
				'Published posts do not have valid author assignments. Ensure all posts have authors with complete profile information.',
				'publishing',
				'medium',
				40,
				'pub-author-set'
			);
		}

		$percentage = ( $posts_with_author_and_bio / count( $posts ) ) * 100;

		// Flag if less than 70% of posts have authors with bios.
		if ( $percentage < 70 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-author-set',
				'Author Profiles Incomplete',
				sprintf(
					'Only %.0f%% of published posts have authors with complete biographical information. Author bios improve credibility and reader trust.',
					$percentage
				),
				'publishing',
				'low',
				25,
				'pub-author-set'
			);
		}

		return null; // All good.
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Author Set
	 * Slug: pub-author-set
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Verifies published posts have valid authors with complete biographical information
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_author_set(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Published posts have proper author attribution and complete author bios.',
			);
		}

		$message = isset( $result['description'] ) ? $result['description'] : 'Author attribution or biographical information is incomplete for published content.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
