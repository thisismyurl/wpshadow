<?php
/**
 * Diagnostic: Pub Heading Hierarchy
 *
 * Checks for proper heading hierarchy (H1 → H2 → H3 structure without gaps)
 * in published content. Ensures content follows accessibility best practices
 * and SEO guidelines for heading structure.
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
 * Diagnostic_Pub_Heading_Hierarchy Class
 *
 * Detects heading hierarchy issues in published content that could
 * negatively impact accessibility and SEO.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Heading_Hierarchy extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-heading-hierarchy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heading Hierarchy Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies proper heading hierarchy (H1 → H2 → H3) in published content for accessibility and SEO.';

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
	 */
	public static function get_id(): string {
		return 'pub-heading-hierarchy';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Heading Hierarchy Correct', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'H1 → H2 → H3 structure (no gaps)?', 'wpshadow' );
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
	 * Executes the heading hierarchy check and returns formatted results.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results with status, message, and data.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'No heading hierarchy issues found', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'] ?? __( 'Heading hierarchy issues detected', 'wpshadow' ),
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-heading-hierarchy';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Check for heading hierarchy issues in published content.
	 *
	 * This diagnostic analyzes the most recent published post or page to ensure
	 * proper heading hierarchy (H1 → H2 → H3 structure without gaps).
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check(): ?array {
		// Get the most recent published post or page.
		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		);

		$posts = get_posts( $args );

		// If no published content, nothing to check.
		if ( empty( $posts ) ) {
			return null;
		}

		$post    = $posts[0];
		$content = $post->post_content;

		// Extract headings from content.
		$headings = self::extract_headings_from_content( $content );

		// If no headings found, pass (nothing to check).
		if ( empty( $headings ) ) {
			return null;
		}

		// Detect hierarchy issues.
		$issues = self::detect_hierarchy_issues( $headings );

		// No issues found = PASS.
		if ( empty( $issues ) ) {
			return null;
		}

		// Calculate threat level based on number of issues.
		$threat_level = 25;
		if ( count( $issues ) > 2 ) {
			$threat_level = 40;
		}

		// Build descriptive message.
		$message = sprintf(
			/* translators: 1: post title, 2: number of issues, 3: list of issues */
			__( 'Your post "%1$s" has %2$d heading hierarchy issue(s): %3$s. Proper heading hierarchy is important for accessibility and SEO.', 'wpshadow' ),
			esc_html( $post->post_title ),
			count( $issues ),
			implode( '; ', array_slice( $issues, 0, 3 ) )
		);

		return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
			'pub-heading-hierarchy',
			__( 'Heading Hierarchy Issues', 'wpshadow' ),
			$message,
			'general',
			'low',
			$threat_level,
			'pub-heading-hierarchy'
		);
	}

	/**
	 * Extract headings from content.
	 *
	 * @since  1.2601.2148
	 * @param  string $content Post content.
	 * @return array Array of heading levels in order.
	 */
	protected static function extract_headings_from_content( string $content ): array {
		if ( empty( $content ) ) {
			return array();
		}

		$headings = array();

		// Match all heading tags (H1-H6).
		preg_match_all( '/<h([1-6])[^>]*>/i', $content, $matches );

		if ( ! empty( $matches[1] ) ) {
			$headings = array_map( 'intval', $matches[1] );
		}

		return $headings;
	}

	/**
	 * Detect heading hierarchy issues.
	 *
	 * @since  1.2601.2148
	 * @param  array $headings Array of heading levels.
	 * @return array Array of issue descriptions.
	 */
	protected static function detect_hierarchy_issues( array $headings ): array {
		$issues = array();

		if ( empty( $headings ) ) {
			return $issues;
		}

		// Check if H1 is present.
		if ( ! in_array( 1, $headings, true ) ) {
			$issues[] = __( 'No H1 heading found', 'wpshadow' );
		}

		// Check for skipped levels by tracking max level seen.
		$max_level_seen = 0;
		$seen_levels    = array();

		foreach ( $headings as $level ) {
			$seen_levels[ $level ] = true;

			// Check if we skipped any levels between max_level_seen and current level.
			if ( $level > $max_level_seen + 1 ) {
				// Find which levels were skipped.
				for ( $expected = $max_level_seen + 1; $expected < $level; $expected++ ) {
					if ( ! isset( $seen_levels[ $expected ] ) ) {
						$issues[] = sprintf(
							/* translators: 1: previous heading level, 2: current heading level */
							__( 'Skipped heading level: H%1$d → H%2$d', 'wpshadow' ),
							$max_level_seen > 0 ? $max_level_seen : 0,
							$level
						);
						break; // Only report once per gap.
					}
				}
			}

			// Update max level seen.
			if ( $level > $max_level_seen ) {
				$max_level_seen = $level;
			}
		}

		// Check for orphan headings - level exists but parent level never appeared before it.
		$levels_in_order = array();
		foreach ( $headings as $level ) {
			if ( ! in_array( $level, $levels_in_order, true ) ) {
				$levels_in_order[] = $level;
			}
		}

		foreach ( $levels_in_order as $level ) {
			if ( $level > 1 ) {
				// Check if parent level appeared before this level in the sequence.
				$parent_level = $level - 1;
				$level_index  = array_search( $level, $levels_in_order, true );
				$parent_index = array_search( $parent_level, $levels_in_order, true );

				if ( false === $parent_index || $parent_index > $level_index ) {
					$issues[] = sprintf(
						/* translators: 1: heading level, 2: parent heading level */
						__( 'Orphan heading: H%1$d found without H%2$d parent', 'wpshadow' ),
						$level,
						$parent_level
					);
				}
			}
		}

		// Remove duplicate issues.
		$issues = array_unique( $issues );

		return $issues;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Heading Hierarchy
	 * Slug: pub-heading-hierarchy
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks for proper heading hierarchy (H1 → H2 → H3) in published content.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_heading_hierarchy(): array {
		$result = self::check();

		// If check() returns null, diagnostic passed (no issues found).
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Diagnostic passed: No heading hierarchy issues found in published content', 'wpshadow' ),
			);
		}

		// If check() returns an array, diagnostic found issues.
		if ( is_array( $result ) && isset( $result['description'] ) ) {
			return array(
				'passed'  => false,
				'message' => sprintf(
					/* translators: %s: issue description */
					__( 'Diagnostic failed: %s', 'wpshadow' ),
					$result['description']
				),
			);
		}

		// Unexpected result format.
		return array(
			'passed'  => false,
			'message' => __( 'Diagnostic test produced unexpected result format', 'wpshadow' ),
		);
	}
}
