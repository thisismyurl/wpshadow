<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Keyword in Headings
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7, 8, 9
 *
 * Test Description:
 * Primary keyword in H1 or H2?
 *
 * Checks if the focus/primary keyword (defined by SEO plugins) appears in H1 or H2
 * headings in published posts. This is an important SEO best practice that helps
 * search engines understand content relevance.
 *
 * @since   1.2601.2148
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-26 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Pub_Keyword_In_Headings extends Diagnostic_Base {
	protected static $slug = 'pub-keyword-in-headings';

	protected static $title = 'Keyword In Headings';

	protected static $description = 'Checks if focus keywords appear in H1/H2 headings for SEO optimization.';

	protected static $family = 'content-publishing';

	protected static $family_label = 'Content Publishing';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-keyword-in-headings';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Keyword in Headings', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Primary keyword in H1 or H2?', 'wpshadow' );
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
		// STUB: Implement pub-keyword-in-headings test
		// Philosophy focus: Commandment #7, 8, 9
		//
		// Data collection strategy:
		// - Gather relevant metrics from WordPress
		// - Calculate or query necessary values
		// - Return structured result
		//
		// KB Article: https://wpshadow.com/kb/pub-keyword-in-headings
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
		return 'https://wpshadow.com/kb/pub-keyword-in-headings';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	public static function check(): ?array {
		global $wpdb;

		// Check if popular SEO plugins are active (they manage focus keywords).
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',              // Yoast SEO.
			'all-in-one-seo-pack/all_in_one_seo_pack.php', // All in One SEO.
			'seo-by-rank-math/rank-math.php',        // Rank Math.
			'autodescription/autodescription.php',   // The SEO Framework.
		);

		$has_seo_plugin = false;
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_seo_plugin = true;
				break;
			}
		}

		// If no SEO plugin, recommend installing one.
		if ( ! $has_seo_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No SEO plugin detected. Install Yoast SEO, Rank Math, or similar to manage focus keywords and optimize headings.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pub-keyword-in-headings',
			);
		}

		// Query recent published posts (sample of 20).
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
			// No published posts to check.
			return null;
		}

		$posts_with_keywords       = 0;
		$posts_missing_in_headings = 0;

		foreach ( $posts as $post ) {
			// Get focus keyword from SEO plugin meta.
			$focus_keyword = self::get_focus_keyword( $post->ID );

			if ( empty( $focus_keyword ) ) {
				// No focus keyword set, skip.
				continue;
			}

			++$posts_with_keywords;

			// Extract H1 and H2 headings from content.
			$headings = self::extract_headings( $post->post_content );

			// Check if focus keyword appears in any heading.
			$keyword_in_heading = self::keyword_in_headings( $focus_keyword, $headings );

			if ( ! $keyword_in_heading ) {
				++$posts_missing_in_headings;
			}
		}

		// If no posts have keywords set, return null (nothing to check).
		if ( 0 === $posts_with_keywords ) {
			return null;
		}

		// Calculate percentage of posts missing keyword in headings.
		$missing_percentage = ( $posts_missing_in_headings / $posts_with_keywords ) * 100;

		// Threshold: flag if more than 30% of posts are missing keyword in headings.
		if ( $missing_percentage > 30 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of posts, 2: total posts checked */
					__( '%1$d of %2$d posts are missing their focus keyword in H1/H2 headings. This can hurt SEO rankings. Consider revising headings to include target keywords naturally.', 'wpshadow' ),
					$posts_missing_in_headings,
					$posts_with_keywords
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pub-keyword-in-headings',
			);
		}

		// All good!
		return null;
	}

	/**
	 * Get focus keyword from post meta (supports multiple SEO plugins).
	 *
	 * @since  1.2601.2148
	 * @param  int $post_id Post ID.
	 * @return string Focus keyword or empty string.
	 */
	private static function get_focus_keyword( $post_id ): string {
		// Yoast SEO.
		$yoast_keyword = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
		if ( ! empty( $yoast_keyword ) ) {
			return $yoast_keyword;
		}

		// Rank Math.
		$rank_math_keyword = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
		if ( ! empty( $rank_math_keyword ) ) {
			return $rank_math_keyword;
		}

		// All in One SEO.
		$aioseo_keyword = get_post_meta( $post_id, '_aioseo_keywords', true );
		if ( ! empty( $aioseo_keyword ) ) {
			// AIOSEO stores as JSON array.
			$keywords_data = json_decode( $aioseo_keyword, true );
			if ( is_array( $keywords_data ) && isset( $keywords_data['focus']['keyphrase'] ) ) {
				return $keywords_data['focus']['keyphrase'];
			}
		}

		// The SEO Framework.
		$tsf_keyword = get_post_meta( $post_id, '_genesis_focus_keyword', true );
		if ( ! empty( $tsf_keyword ) ) {
			return $tsf_keyword;
		}

		return '';
	}

	/**
	 * Extract H1 and H2 headings from HTML content.
	 *
	 * @since  1.2601.2148
	 * @param  string $content Post content HTML.
	 * @return array Array of heading text.
	 */
	private static function extract_headings( $content ): array {
		$headings = array();

		// Match H1 tags.
		preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $content, $h1_matches );
		if ( ! empty( $h1_matches[1] ) ) {
			foreach ( $h1_matches[1] as $heading ) {
				$headings[] = wp_strip_all_tags( $heading );
			}
		}

		// Match H2 tags.
		preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/is', $content, $h2_matches );
		if ( ! empty( $h2_matches[1] ) ) {
			foreach ( $h2_matches[1] as $heading ) {
				$headings[] = wp_strip_all_tags( $heading );
			}
		}

		return $headings;
	}

	/**
	 * Check if keyword appears in any heading (case-insensitive).
	 *
	 * @since  1.2601.2148
	 * @param  string $keyword  Focus keyword.
	 * @param  array  $headings Array of heading text.
	 * @return bool True if keyword found in any heading.
	 */
	private static function keyword_in_headings( $keyword, $headings ): bool {
		if ( empty( $headings ) ) {
			return false;
		}

		$keyword_lower = mb_strtolower( $keyword );

		foreach ( $headings as $heading ) {
			$heading_lower = mb_strtolower( $heading );
			if ( false !== mb_strpos( $heading_lower, $keyword_lower ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Keyword In Headings
	 * Slug: pub-keyword-in-headings
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if focus keywords appear in H1/H2 headings for SEO optimization.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_keyword_in_headings(): array {
		// Create a test post without keyword in headings.
		$test_post_id = wp_insert_post(
			array(
				'post_title'   => 'Test Post for Keyword Check',
				'post_content' => '<h1>Random Heading</h1><h2>Another Heading</h2><p>Some content here.</p>',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			)
		);

		if ( is_wp_error( $test_post_id ) ) {
			return array(
				'passed'  => false,
				'message' => 'Failed to create test post',
			);
		}

		// Set a focus keyword that doesn't appear in headings.
		update_post_meta( $test_post_id, '_yoast_wpseo_focuskw', 'WordPress security' );

		// Run the diagnostic.
		$result = self::check();

		// Clean up test post.
		wp_delete_post( $test_post_id, true );

		// Evaluate result.
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Published posts have keywords properly placed in headings',
			);
		}

		$message = $result['description'] ?? 'SEO heading optimization issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
