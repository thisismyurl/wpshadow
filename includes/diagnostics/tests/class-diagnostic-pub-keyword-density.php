<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Keyword Density
 *
 * Analyzes keyword density in recent published posts to ensure primary keywords
 * appear naturally within the optimal SEO range (0.5-2.5%). This helps avoid
 * both keyword stuffing (over-optimization) and insufficient keyword usage.
 *
 * The diagnostic checks for keywords in this order:
 * 1. Yoast SEO focus keyword (_yoast_wpseo_focuskw)
 * 2. Rank Math focus keyword (rank_math_focus_keyword)
 * 3. Fallback: Longest meaningful word from post title
 *
 * Category: Content Publishing
 * Priority: 2 (Medium)
 * Philosophy: 7 (Inspire Confidence), 8 (Everything Has a KPI), 9 (Talk-About-Worthy)
 *
 * Test Description:
 * Primary keyword appears naturally (0.5-2.5%)?
 *
 * @since   1.2601.2148
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-26 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Pub_Keyword_Density extends Diagnostic_Base {
	protected static $slug = 'pub-keyword-density';

	protected static $title = 'Pub Keyword Density';

	protected static $description = 'Automatically initialized lean diagnostic for Pub Keyword Density. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Common English stop words to exclude from keyword extraction.
	 *
	 * @since 1.2601.2148
	 * @var array
	 */
	const STOP_WORDS = array( 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did' );

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-keyword-density';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Keyword Density', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Checks if primary keywords appear naturally in content (optimal: 0.5-2.5% density)', 'wpshadow' );
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
		// STUB: Implement pub-keyword-density test
		// Philosophy focus: Commandment #7, 8, 9
		//
		// Data collection strategy:
		// - Gather relevant metrics from WordPress
		// - Calculate or query necessary values
		// - Return structured result
		//
		// KB Article: https://wpshadow.com/kb/pub-keyword-density
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
		return 'https://wpshadow.com/kb/pub-keyword-density';
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
	 * Analyzes keyword density in recent published posts to ensure primary keywords
	 * appear naturally (0.5-2.5%). This helps with SEO while avoiding keyword stuffing.
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

		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_issues = 0;
		$total_posts       = count( $posts );

		foreach ( $posts as $post ) {
			// Extract primary keyword.
			$primary_keyword = self::get_primary_keyword( $post );

			if ( empty( $primary_keyword ) ) {
				continue;
			}

			// Calculate keyword density.
			$density = self::calculate_keyword_density( $post->post_content, $primary_keyword );

			// Check if density is outside optimal range (0.5% - 2.5%).
			if ( $density < 0.5 || $density > 2.5 ) {
				++$posts_with_issues;
			}
		}

		// Flag if more than 30% of posts have sub-optimal keyword density.
		if ( $total_posts > 0 ) {
			$issue_percentage = ( $posts_with_issues / $total_posts ) * 100;
		} else {
			$issue_percentage = 0;
		}

		if ( $issue_percentage > 30 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'pub-keyword-density',
				'Keyword Density Issues',
				sprintf(
					/* translators: %d: percentage of posts with issues */
					__( '%d%% of recent posts have keyword density outside the optimal 0.5-2.5%% range. This may impact SEO performance.', 'wpshadow' ),
					(int) round( $issue_percentage )
				),
				'general',
				'low',
				25,
				'pub-keyword-density'
			);
		}

		return null;
	}

	/**
	 * Extract primary keyword from post
	 *
	 * Attempts to get keyword from SEO plugins (Yoast, Rank Math), then falls back
	 * to extracting most significant words from the title.
	 *
	 * @since  1.2601.2148
	 * @param  \WP_Post $post Post object to extract keyword from.
	 * @return string Primary keyword or empty string.
	 */
	private static function get_primary_keyword( $post ): string {
		// Try Yoast SEO meta.
		$yoast_keyword = get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true );
		if ( ! empty( $yoast_keyword ) ) {
			return sanitize_text_field( $yoast_keyword );
		}

		// Try Rank Math meta.
		$rankmath_keyword = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );
		if ( ! empty( $rankmath_keyword ) ) {
			return sanitize_text_field( $rankmath_keyword );
		}

		// Fallback: Extract most significant word from title (longest meaningful word).
		$title = $post->post_title;
		if ( empty( $title ) ) {
			return '';
		}

		// Remove common stop words and get longest word.
		$words = preg_split( '/\s+/', strtolower( $title ) );
		$words = array_filter(
			$words,
			function ( $word ) {
				return strlen( $word ) > 3 && ! in_array( $word, self::STOP_WORDS, true );
			}
		);

		if ( empty( $words ) ) {
			return '';
		}

		// Return longest word as primary keyword.
		usort(
			$words,
			function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);

		return $words[0];
	}

	/**
	 * Calculate keyword density in content
	 *
	 * Calculates the percentage of times a keyword appears in the content.
	 * Uses word-boundary matching to avoid partial word matches.
	 *
	 * @since  1.2601.2148
	 * @param  string $content Content to analyze.
	 * @param  string $keyword Keyword to search for.
	 * @return float Keyword density as percentage (0-100).
	 */
	private static function calculate_keyword_density( string $content, string $keyword ): float {
		// Strip HTML tags and shortcodes.
		$clean_content = wp_strip_all_tags( strip_shortcodes( $content ) );

		// Count total words.
		$total_words = str_word_count( $clean_content );

		if ( $total_words < 100 ) {
			return 0.0;
		}

		// Count keyword occurrences using word boundaries to avoid partial matches.
		$keyword_lower = preg_quote( strtolower( $keyword ), '/' );
		$content_lower = strtolower( $clean_content );

		// Match whole words only using word boundaries.
		$pattern = '/\b' . $keyword_lower . '\b/';
		$matches = preg_match_all( $pattern, $content_lower );

		if ( false === $matches ) {
			return 0.0;
		}

		// Calculate density as percentage.
		$density = ( $matches / $total_words ) * 100;

		return round( $density, 2 );
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Validates that the keyword density diagnostic correctly identifies posts
	 * with sub-optimal keyword density (outside 0.5-2.5% range).
	 *
	 * Diagnostic: Pub Keyword Density
	 * Slug: pub-keyword-density
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when keyword density is optimal across posts
	 * - FAIL: check() returns array when >30% of posts have sub-optimal density
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_keyword_density(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Published posts have optimal keyword density for SEO (0.5-2.5% range)', 'wpshadow' ),
			);
		}

		$message = $result['description'] ?? __( 'Keyword density optimization issue detected', 'wpshadow' );

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
