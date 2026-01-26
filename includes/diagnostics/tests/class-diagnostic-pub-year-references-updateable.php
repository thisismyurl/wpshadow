<?php
/**
 * Diagnostic: Year References Updateable
 *
 * Checks if content contains year-specific references and whether they're
 * in an easily updateable format (shortcodes, custom fields) rather than
 * hard-coded text that requires manual updates annually.
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
 * Diagnostic_Pub_Year_References_Updateable Class
 *
 * Detects content with year-specific references and flags cases where
 * years are hard-coded instead of using maintainable patterns like
 * shortcodes or custom fields.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Year_References_Updateable extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-year-references-updateable';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Year References Updateable';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if year-specific content uses updateable patterns rather than hard-coded years.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-publishing';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Content Publishing';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'pub-year-references-updateable';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Year References Are Updateable', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Checks if content has year references in an easily updatable format (shortcodes, custom fields) rather than hard-coded text.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * Low threat - this is a content maintainability issue, not a security or performance problem.
	 *
	 * @since  1.2601.2148
	 * @return int Threat level 0-100 (25 = low).
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test
	 *
	 * Wrapper around check() for backward compatibility with different diagnostic APIs.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results.
	 */
	public static function run(): array {
		$finding = self::check();

		if ( null === $finding ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Year references are updateable or not present', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'warning',
			'message' => $finding['description'],
			'data'    => $finding,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-year-references-updateable';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/content-publishing-year-references';
	}

	/**
	 * Run the diagnostic check
	 *
	 * Checks if content contains year-specific references and whether they're
	 * in an easily updateable format (shortcodes, custom fields) rather than
	 * hard-coded text.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check(): ?array {
		// Get recent published posts.
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			// No posts to check.
			return null;
		}

		$current_year         = (int) gmdate( 'Y' );
		$posts_with_years     = 0;
		$posts_hardcoded      = 0;
		$posts_with_patterns  = 0;

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for year patterns in the current and recent years (last 3 years).
			$year_pattern = sprintf(
				'/\b(%d|%d|%d)\b/',
				$current_year,
				$current_year - 1,
				$current_year - 2
			);

			if ( ! preg_match( $year_pattern, $content ) ) {
				// No year references in this post.
				continue;
			}

			++$posts_with_years;

			// Check for updateable patterns (shortcodes that might handle years).
			$has_year_shortcode = (
				strpos( $content, '[year' ) !== false ||
				strpos( $content, '[current_year' ) !== false ||
				strpos( $content, '[date' ) !== false
			);

			// Check for year in custom fields (common pattern for dynamic content).
			$year_meta = get_post_meta( $post->ID, 'year', true ) ||
						get_post_meta( $post->ID, 'publication_year', true ) ||
						get_post_meta( $post->ID, 'reference_year', true );

			if ( $has_year_shortcode || ! empty( $year_meta ) ) {
				++$posts_with_patterns;
			} else {
				// Has year references but no updateable patterns.
				++$posts_hardcoded;
			}
		}

		// If less than 20% of posts have year references, not a concern.
		if ( 0 === $posts_with_years ) {
			return null;
		}

		$hardcoded_percentage = ( $posts_hardcoded / $posts_with_years ) * 100;

		// Flag if more than 70% of posts with year references are hard-coded.
		if ( $hardcoded_percentage > 70 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-year-references-updateable',
				/* translators: %d: percentage of posts with hard-coded years */
				sprintf( __( 'Year References Not Easily Updateable', 'wpshadow' ) ),
				sprintf(
					/* translators: 1: number of posts with hard-coded years, 2: percentage */
					__( 'Found %1$d posts with year-specific content (%.0f%% hard-coded). Consider using shortcodes like [current_year] or custom fields instead of hard-coding years. This makes annual updates much easier and reduces the risk of outdated content.', 'wpshadow' ),
					$posts_hardcoded,
					$hardcoded_percentage
				),
				'general',
				'low',
				25,
				'pub-year-references-updateable'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Verifies that the check() method returns the correct result based on site state.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result array.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_pub_year_references_updateable(): array {
		$result = self::check();

		if ( null === $result ) {
			// No issues found - posts either don't have year references or use updateable patterns.
			return array(
				'passed'  => true,
				'message' => __( 'Year references are either absent or use updateable patterns (shortcodes, custom fields).', 'wpshadow' ),
			);
		}

		// Issues found - too many hard-coded year references.
		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: %s: diagnostic title */
				__( 'Diagnostic "%s" detected issues: Content contains hard-coded year references that should be converted to updateable patterns.', 'wpshadow' ),
				self::$title
			),
		);
	}
}
