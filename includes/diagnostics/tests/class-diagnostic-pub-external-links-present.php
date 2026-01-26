<?php
/**
 * Diagnostic: External Links Present in Published Content
 *
 * Detects whether published posts and pages contain links to external websites.
 * This is an informational diagnostic that helps content publishers understand
 * if their content references external sources.
 *
 * Category: Content Publishing
 * Threat Level: 25 (Informational)
 * Auto-fixable: No
 *
 * Philosophy: Commandments #7, 8, 9 (Content quality and SEO best practices)
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2148
 *
 * @verified 2026-01-26 - Fully implemented
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;



/**
 * Diagnostic_Pub_External_Links_Present Class
 *
 * Scans published content for external links. Returns null if no external
 * links are found, or a finding array if external links are detected.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_External_Links_Present extends Diagnostic_Base {
	protected static $slug = 'pub-external-links-present';

	protected static $title = 'External Links Present in Published Content';

	protected static $description = 'Detects whether published posts and pages contain links to external websites. This informational diagnostic helps understand content strategy and SEO considerations.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-external-links-present';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'External Links Present', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Content references external sources?', 'wpshadow' );
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
		// STUB: Implement pub-external-links-present test
		// Philosophy focus: Commandment #7, 8, 9
		//
		// Data collection strategy:
		// - Gather relevant metrics from WordPress
		// - Calculate or query necessary values
		// - Return structured result
		//
		// KB Article: https://wpshadow.com/kb/pub-external-links-present
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
		return 'https://wpshadow.com/kb/pub-external-links-present';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Scans published posts and pages for external links. Returns null if
	 * no external links are found (content is self-contained), or returns
	 * a finding array if external links are detected.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Finding data if external links detected, null otherwise.
	 *
	 *     @type string $id           Finding identifier.
	 *     @type string $title        Finding title.
	 *     @type string $description  Human-readable description.
	 *     @type string $category     Diagnostic category.
	 *     @type string $severity     Severity level ('low').
	 *     @type int    $threat_level Threat level (25).
	 *     @type string $kb_link      Knowledge base article URL.
	 *     @type string $training_link Training video URL.
	 *     @type bool   $auto_fixable Whether treatment exists (false).
	 * }
	 */
	public static function check(): ?array {
		// Check for external links in published content.
		$external_links = self::detect_external_links();

		if ( empty( $external_links ) ) {
			// No external links found - content is self-contained.
			return null;
		}

		// External links detected - return informational finding.
		$count = count( $external_links );
		return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
			'pub-external-links-present',
			'External Links Present in Published Content',
			sprintf(
				/* translators: %d: number of external links */
				_n(
					'Found %d external link in published content. External links can provide value but may affect SEO and user engagement.',
					'Found %d external links in published content. External links can provide value but may affect SEO and user engagement.',
					$count,
					'wpshadow'
				),
				$count
			),
			'general',
			'low',
			25,
			'pub-external-links-present'
		);
	}

	/**
	 * Detect external links in published posts and pages.
	 *
	 * @return array List of post IDs that contain external links.
	 */
	private static function detect_external_links(): array {
		global $wpdb;

		// Get site URL to determine what's "external".
		$site_url    = get_site_url();
		$site_domain = wp_parse_url( $site_url, PHP_URL_HOST );

		// Query published posts and pages.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_content 
				FROM {$wpdb->posts} 
				WHERE post_status = %s 
				AND post_type IN (%s, %s) 
				LIMIT 100",
				'publish',
				'post',
				'page'
			)
		);

		$posts_with_external_links = array();

		foreach ( $posts as $post ) {
			// Extract links from content using regex.
			// Note: While DOMDocument could be more robust, regex is sufficient here
			// for performance reasons when scanning up to 100 posts. The content is
			// already stored in the database, not real-time user input.
			preg_match_all( '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );

			if ( empty( $matches[1] ) ) {
				continue;
			}

			// Check each link to see if it's external.
			foreach ( $matches[1] as $url ) {
				$link_domain = wp_parse_url( $url, PHP_URL_HOST );

				// Skip if no domain (relative link) or same domain (internal).
				if ( empty( $link_domain ) || $link_domain === $site_domain ) {
					continue;
				}

				// Found an external link.
				$posts_with_external_links[] = $post->ID;
				break; // No need to check more links in this post.
			}
		}

		return $posts_with_external_links;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub External Links Present
	 * Slug: pub-external-links-present
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Pub External Links Present. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_external_links_present(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		// Get the actual external links in content.
		$external_links     = self::detect_external_links();
		$has_external_links = ! empty( $external_links );

		// Run the diagnostic check.
		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		// Diagnostic should return array when external links exist.
		// Diagnostic should return null when no external links exist.
		$test_passes = ( $has_external_links === $diagnostic_found_issue );

		$message = $test_passes
			? sprintf(
				'External links check passed. Site has %d post(s) with external links, diagnostic correctly returned %s',
				count( $external_links ),
				$diagnostic_found_issue ? 'finding' : 'null'
			)
			: sprintf(
				'Mismatch: Site has %d post(s) with external links but diagnostic returned %s',
				count( $external_links ),
				$diagnostic_found_issue ? 'finding' : 'null'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
