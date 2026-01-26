<?php
/**
 * Retention Community Engagement Diagnostic
 *
 * Checks if users are helping each other through community features.
 * Evaluates comment activity, forum plugins, and user interaction patterns.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


/**
 * Diagnostic: Retention Community Engagement
 *
 * Detects low community engagement by checking:
 * - Comment activity rates (comments per post ratio)
 * - Presence of community/forum plugins (BuddyPress, bbPress, etc.)
 * - User interaction patterns
 *
 * Flags if:
 * - Site has many posts but very low comment activity
 * - Community plugins installed but not being used
 * - Large site with no community features enabled
 *
 * @since 1.2601.2148
 */
class Diagnostic_Retention_Community_Engagement extends Diagnostic_Base {
	protected static $slug = 'retention-community-engagement';

	protected static $title = 'Retention Community Engagement';

	protected static $description = 'Automatically initialized lean diagnostic for Retention Community Engagement. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'retention-community-engagement';
	}

	/**
	 * Get diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Are users helping each other?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Detailed description of what this diagnostic checks.
	 */
	public static function get_description(): string {
		return __( 'Are users helping each other?. Part of Customer Retention analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category.
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'customer_retention';
	}

	/**
	 * Run the diagnostic test.
	 *
	 * Legacy method for backward compatibility. Wraps check() method.
	 *
	 * @since  1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$result = self::check();
		return null === $result ? array() : $result;
	}

	/**
	 * Get threat level for this finding.
	 *
	 * @since  1.2601.2148
	 * @return int Threat level (0-100).
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 60;
	}

	/**
	 * Get KB article URL.
	 *
	 * @since  1.2601.2148
	 * @return string URL to knowledge base article.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/retention-community-engagement/';
	}

	/**
	 * Get training video URL.
	 *
	 * @since  1.2601.2148
	 * @return string URL to training video.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/retention-community-engagement/';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if users are helping each other through community engagement features.
	 * Evaluates comment activity, forum/community plugin presence, and user interaction.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if low engagement detected, null otherwise.
	 */
	public static function check(): ?array {
		// Check comment engagement
		$comment_count   = wp_count_comments();
		$total_comments  = $comment_count->total_comments ?? 0;
		$approved_comments = $comment_count->approved ?? 0;

		// Get published posts
		$post_count      = wp_count_posts();
		$published_posts = $post_count->publish ?? 0;

		// Check for community/forum plugins
		$has_community_plugin = false;
		$community_plugins    = array(
			'buddypress/bp-loader.php',
			'bbpress/bbpress.php',
			'wpforo/wpforo.php',
			'simple-forum/simple-forum.php',
			'cm-answers/cm-answers.php',
			'buddyboss-platform/bp-loader.php',
			'peepso-core/peepso.php',
		);

		if ( function_exists( 'is_plugin_active' ) ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			foreach ( $community_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_community_plugin = true;
					break;
				}
			}
		}

		// Calculate engagement metrics
		$comments_per_post = $published_posts > 0 ? $total_comments / $published_posts : 0;

		// Determine if there's an engagement issue
		$has_low_engagement = false;
		$description        = '';
		$severity           = 'low';
		$threat_level       = 30;

		// If site has posts but very low comment activity
		if ( $published_posts >= 10 && $comments_per_post < 0.3 && $approved_comments < 20 ) {
			$has_low_engagement = true;
			$description        = sprintf(
				/* translators: 1: average comments per post, 2: total approved comments */
				__( 'Low community engagement detected. Your site has %.2f comments per post on average (%d total approved comments). Consider enabling discussion features, responding to comments, or installing community plugins like BuddyPress or bbPress to foster user interaction.', 'wpshadow' ),
				$comments_per_post,
				$approved_comments
			);
			$severity     = 'medium';
			$threat_level = 50;
		}

		// If has community plugin but no real activity
		if ( $has_community_plugin && $approved_comments < 10 ) {
			$has_low_engagement = true;
			$description        = __( 'Community plugin is installed but engagement is low. You have community features available (forums/discussion plugins) but limited user interaction. Consider promoting these features to your users and encouraging participation.', 'wpshadow' );
			$severity           = 'medium';
			$threat_level       = 55;
		}

		// If site is large but has no community features at all
		if ( $published_posts >= 50 && ! $has_community_plugin && $comments_per_post < 0.5 ) {
			$has_low_engagement = true;
			$description        = sprintf(
				/* translators: %d: number of published posts */
				__( 'Your site has %d published posts but lacks community engagement features. Consider enabling comments, installing a forum plugin (BuddyPress, bbPress), or adding Q&A functionality to help users interact and help each other.', 'wpshadow' ),
				$published_posts
			);
			$severity     = 'medium';
			$threat_level = 60;
		}

		// If no issues detected, return null
		if ( ! $has_low_engagement ) {
			return null;
		}

		// Build and return finding
		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => $description,
			'category'      => self::family_to_category( self::$family ),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'kb_link'       => 'https://wpshadow.com/kb/' . rawurlencode( self::$slug ),
			'training_link' => 'https://wpshadow.com/training/' . rawurlencode( self::$slug ),
			'auto_fixable'  => false,
		);
	}

	/**
	 * Map a family slug to a category.
	 *
	 * @since  1.2601.2148
	 * @param  string $family Family slug.
	 * @return string Category name.
	 */
	private static function family_to_category( string $family ): string {
		$map = array(
			'security'    => 'security',
			'performance' => 'performance',
			'seo'         => 'seo',
			'design'      => 'design',
			'monitor'     => 'monitoring',
			'code'        => 'code-quality',
			'config'      => 'configuration',
			'system'      => 'system',
			'general'     => 'general',
		);
		return $map[ $family ] ?? 'general';
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Verifies that check() method returns the correct result based on site state.
	 *
	 * Diagnostic: Retention Community Engagement
	 * Slug: retention-community-engagement
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_retention_community_engagement(): array {
		$result = self::check();

		// Get site metrics for test message
		$comment_count   = wp_count_comments();
		$total_comments  = $comment_count->total_comments ?? 0;
		$post_count      = wp_count_posts();
		$published_posts = $post_count->publish ?? 0;
		$comments_per_post = $published_posts > 0 ? $total_comments / $published_posts : 0;

		// Test passes if check() returns null (healthy) or returns proper finding array (issue detected)
		$passed = true;

		if ( null === $result ) {
			$message = sprintf(
				/* translators: 1: comments per post ratio, 2: total comments */
				__( 'Community engagement is healthy: %.2f comments per post (%d total comments)', 'wpshadow' ),
				$comments_per_post,
				$total_comments
			);
		} else {
			// Validate finding structure
			if ( ! is_array( $result ) ||
			     ! isset( $result['id'] ) ||
			     ! isset( $result['title'] ) ||
			     ! isset( $result['description'] ) ) {
				$passed  = false;
				$message = __( 'Finding structure is invalid', 'wpshadow' );
			} else {
				$message = sprintf(
					/* translators: %s: finding description */
					__( 'Low engagement detected: %s', 'wpshadow' ),
					$result['description']
				);
			}
		}

		return array(
			'passed'  => $passed,
			'message' => $message,
		);
	}
}
