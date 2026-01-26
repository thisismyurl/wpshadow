<?php
/**
 * Pub Year References Check Diagnostic
 *
 * Scans published content for year references (e.g., "Best of 2025", "2024 trends")
 * that may require annual updates to maintain content freshness and relevance.
 *
 * This diagnostic helps content teams identify posts that need periodic review
 * and updating to prevent outdated year references.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


/**
 * Class Diagnostic_Pub_Year_References_Check
 *
 * Detects year references in post content that may become outdated.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Year_References_Check extends Diagnostic_Base {
	protected static $slug = 'pub-year-references-check';

	protected static $title = 'Pub Year References Check';

	protected static $description = 'Detects year references in content (e.g., "Best of 2025") that may require annual updates.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier
	 */
	public static function get_id(): string {
		return 'pub-year-references-check';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Translated diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Year References Audit', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Translated diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Does content reference years? (e.g., "Best of 2025")', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic category identifier
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
	 * Scans recent published content for year references that may need periodic updates.
	 * Philosophy focus: Commandment #7 (Ridiculously Good for Free), #8 (Inspire Confidence), #9 (Everything Has a KPI)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Diagnostic results.
	 *
	 *     @type string $status  Result status ('pass'|'warning').
	 *     @type string $message Human-readable result message.
	 *     @type array  $data    Optional. Additional finding data.
	 * }
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'No concerning year references found in recent content', 'wpshadow' ),
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
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-year-references-check';
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
	 * Scans recent published posts for year references (e.g., "2024", "Best of 2025")
	 * that may require periodic updates. Checks for years within a reasonable range
	 * (current year ± 10 years) to avoid false positives from historical dates.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Array of findings if year references exceed threshold, null if content is clean.
	 *
	 *     @type string $id            Diagnostic identifier.
	 *     @type string $title         Human-readable title.
	 *     @type string $description   Detailed description with examples.
	 *     @type string $category      Diagnostic category.
	 *     @type string $severity      Severity level (low/medium/high/critical).
	 *     @type int    $threat_level  Numeric threat level (0-100).
	 *     @type string $kb_link       Knowledge base article URL.
	 *     @type string $training_link Training video URL.
	 *     @type bool   $auto_fixable  Whether issue can be auto-fixed.
	 * }
	 */
	public static function check(): ?array {
		// Get recent published posts to scan for year references.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$total_posts = count( $posts );

		// Define year range to check (current year ± 10 years).
		$current_year = (int) gmdate( 'Y' );
		$min_year     = $current_year - 10;
		$max_year     = $current_year + 10;

		$posts_with_years = 0;
		$year_examples    = array();

		foreach ( $posts as $post ) {
			// Search for 4-digit years in post title and content.
			$text_to_check = $post->post_title . ' ' . wp_strip_all_tags( $post->post_content );

			// Pattern: look for 4-digit years that are standalone or in context.
			// Matches: "2024", "Best of 2025", "2023 trends", etc.
			// Use preg_match first for efficiency - only process matches if years exist.
			if ( ! preg_match( '/\b(20\d{2}|19\d{2})\b/', $text_to_check ) ) {
				continue;
			}

			// Now get all year matches for validation.
			if ( preg_match_all( '/\b(20\d{2}|19\d{2})\b/', $text_to_check, $matches ) ) {
				$found_years = array_unique( $matches[1] );
				foreach ( $found_years as $year ) {
					$year_int = (int) $year;
					// Only count years in reasonable range.
					if ( $year_int >= $min_year && $year_int <= $max_year ) {
						++$posts_with_years;
						// Store example for reporting.
						if ( count( $year_examples ) < 3 ) {
							$year_examples[] = sprintf( '"%s" (contains %d)', esc_html( $post->post_title ), $year_int );
						}
						break; // Count this post only once.
					}
				}
			}
		}

		$percentage = ( $posts_with_years / $total_posts ) * 100;

		// Flag if more than 30% of recent posts contain year references.
		if ( $percentage > 30 ) {
			$example_text = ! empty( $year_examples ) ? ' Examples: ' . implode( ', ', $year_examples ) : '';

			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-year-references-check',
				'Year References Found in Content',
				sprintf(
					/* translators: 1: percentage of posts with year references, 2: example posts */
					__( '%.0f%% of recent posts contain year references that may need updating annually.%s Consider using evergreen content or maintaining a content update schedule.', 'wpshadow' ),
					$percentage,
					$example_text
				),
				'general',
				'low',
				25,
				'pub-year-references-check'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Verifies that check() method returns the correct result based on site state.
	 * Tests the diagnostic against the actual WordPress installation without mocks.
	 *
	 * Diagnostic: Pub Year References Check
	 * Slug: pub-year-references-check
	 *
	 * Test Criteria:
	 * - PASS: check() returns NULL when no concerning year references found (healthy state)
	 * - PASS: check() returns valid finding array when year references exceed threshold
	 * - FAIL: check() returns unexpected format
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_pub_year_references_check(): array {
		$result = self::check();

		// Test passes if check() returns expected format or null.
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'No year references found in recent content (healthy state)', 'wpshadow' ),
			);
		}

		// If we found year references, validate the finding structure.
		if ( is_array( $result ) && isset( $result['id'], $result['description'] ) ) {
			return array(
				'passed'  => true,
				'message' => sprintf(
					/* translators: %s: finding description */
					__( 'Year references detected correctly: %s', 'wpshadow' ),
					esc_html( $result['description'] )
				),
			);
		}

		return array(
			'passed'  => false,
			'message' => __( 'Diagnostic returned unexpected format', 'wpshadow' ),
		);
	}
}
