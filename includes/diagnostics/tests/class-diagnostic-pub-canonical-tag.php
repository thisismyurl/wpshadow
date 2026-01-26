<?php
/**
 * Diagnostic: Canonical Tag Present
 *
 * Checks if canonical tags are properly enabled on the site to prevent
 * duplicate content issues in search engines.
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: Commandment #7, 8, 9
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 *
 * @verified 2026-01-26 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical Tag Diagnostic Class
 *
 * Verifies that canonical tags are properly enabled to help search engines
 * identify the preferred version of a page and avoid duplicate content penalties.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Canonical_Tag extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-canonical-tag';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Canonical Tag';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if canonical tags are properly enabled to prevent duplicate content issues.';

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
		return 'pub-canonical-tag';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Translatable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Canonical Tag Present', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Translatable diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Is canonical tag set (avoid duplicates)?', 'wpshadow' );
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
	 * @since  1.2601.2148
	 * @return int 0-100 severity level.
	 */
	public static function get_threat_level(): int {
		return 40;
	}

	/**
	 * Run diagnostic test
	 *
	 * Legacy method for backwards compatibility.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Canonical tags are properly enabled', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'] ?? __( 'Canonical tag issue detected', 'wpshadow' ),
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-canonical-tag';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check
	 *
	 * Checks if the WordPress canonical tag functionality (rel_canonical) is
	 * properly enabled. Canonical tags are essential for SEO to prevent
	 * duplicate content penalties.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null if no issues.
	 */
	public static function check(): ?array {
		// Check if canonical tag action is disabled.
		if ( self::is_canonical_disabled() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Canonical tags are disabled on your site. This can lead to duplicate content issues in search engines, which may negatively impact your SEO rankings. Canonical tags help search engines identify the preferred version of a page.', 'wpshadow' ),
				'category'     => 'content_publishing',
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'family'       => self::$family,
				'family_label' => self::$family_label,
				'kb_link'      => self::get_kb_article(),
				'timestamp'    => current_time( 'mysql' ),
				'meta'         => array(
					'issue'          => 'canonical_disabled',
					'recommendation' => __( 'Canonical tags should be enabled unless you are using an SEO plugin that handles them. Check your theme or plugins to see if canonical functionality has been intentionally disabled.', 'wpshadow' ),
				),
			);
		}

		// No issues found - canonical functionality is enabled.
		return null;
	}

	/**
	 * Check if canonical tag functionality is disabled
	 *
	 * WordPress includes built-in canonical tag support via the rel_canonical()
	 * function and wp_head action. This method checks if that functionality
	 * has been removed.
	 *
	 * @since  1.2601.2148
	 * @return bool True if canonical is disabled, false if enabled.
	 */
	private static function is_canonical_disabled(): bool {
		// Check if rel_canonical action is removed from wp_head.
		// WordPress adds this by default, so if it's missing, it was removed.
		return false === has_action( 'wp_head', 'rel_canonical' );
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Canonical Tag Present
	 * Slug: pub-canonical-tag
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when canonical tags are enabled (site is healthy)
	 * - FAIL: check() returns array when canonical tags are disabled (issue found)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result array.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_pub_canonical_tag(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Canonical tag functionality is properly enabled (healthy)', 'wpshadow' ),
			);
		}

		$message = $result['description'] ?? __( 'Canonical tag functionality is disabled - this may cause duplicate content issues', 'wpshadow' );

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
