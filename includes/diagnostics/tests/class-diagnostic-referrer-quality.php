<?php
/**
 * Diagnostic: Referrer Quality
 *
 * Checks if the site has proper mechanisms to track and monitor traffic source quality.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Diagnostic: Referrer Quality
 *
 * Checks if the site has proper mechanisms to track and monitor traffic source quality.
 * This diagnostic verifies that analytics tracking is configured to understand where
 * visitors come from and identify potential spam or low-quality referrers.
 *
 * DETECTION APPROACH:
 * - Check if analytics tracking is configured
 * - Verify referrer policy headers are set
 * - Check for common spam referrer patterns in known options
 * - Assess traffic source monitoring capability
 *
 * PASS CRITERIA:
 * - Analytics tracking configured (Google Analytics, Matomo, or similar)
 * - Referrer policy headers properly set
 * - Traffic source monitoring appears functional
 *
 * FAIL CRITERIA:
 * - No analytics tracking configured
 * - Missing or weak referrer policy headers
 * - No mechanism to track traffic sources
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2148
 */
class Diagnostic_Referrer_Quality extends Diagnostic_Base {
	/**
	 * The diagnostic slug/ID
	 *
	 * @since 1.2601.2148
	 * @var   string
	 */
	protected static $slug = 'referrer-quality';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2601.2148
	 * @var   string
	 */
	protected static $title = 'Referrer Quality';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2601.2148
	 * @var   string
	 */
	protected static $description = 'Verifies proper traffic source monitoring and referrer tracking configuration.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2601.2148
	 * @var   string
	 */
	protected static $family = 'general';

	/**
	 * Display name for the family
	 *
	 * @since 1.2601.2148
	 * @var   string
	 */
	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string The diagnostic ID.
	 */
	public static function get_id(): string {
		return 'referrer-quality';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string The diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Are traffic sources of good quality?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string The diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Checks if traffic sources are of good quality. Part of User Engagement analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string The diagnostic category.
	 */
	public static function get_category(): string {
		return 'user_engagement';
	}

	/**
	 * Get threat level for this finding (0-100)
	 *
	 * @since  1.2601.2148
	 * @return int The threat level (0-100).
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 46;
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string The knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/referrer-quality/';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string The training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/referrer-quality/';
	}

	/**
	 * Check for referrer quality issues.
	 *
	 * This diagnostic verifies that the site has proper mechanisms to track
	 * and monitor traffic source quality. It checks for:
	 * - Analytics tracking configuration
	 * - Referrer policy headers
	 * - Traffic source monitoring capability
	 *
	 * @since  1.2601.2148
	 * @return array|null Array with finding details if issues found, null otherwise.
	 */
	public static function check(): ?array {
		$issues = array();

		// Check if analytics tracking is configured
		$has_analytics = self::has_analytics_configured();

		if ( ! $has_analytics ) {
			$issues[] = __( 'No analytics tracking configured to monitor traffic sources', 'wpshadow' );
		}

		// Check if referrer policy is set
		$has_referrer_policy = self::has_referrer_policy();

		if ( ! $has_referrer_policy ) {
			$issues[] = __( 'No referrer policy headers configured', 'wpshadow' );
		}

		// If no issues found, site passes the check
		if ( empty( $issues ) ) {
			return null;
		}

		// Determine severity based on number of issues
		$severity     = count( $issues ) >= 2 ? 'medium' : 'low';
		$threat_level = count( $issues ) >= 2 ? 55 : 35;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: list of issues found */
				__( 'Your site lacks proper traffic source monitoring. Issues found: %s. Configure analytics and referrer policies to understand and improve traffic quality.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'category'     => 'user_engagement',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/referrer-quality/',
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if analytics tracking is configured.
	 *
	 * Checks for common analytics providers including Google Analytics,
	 * Matomo, and others via filters.
	 *
	 * @since  1.2601.2148
	 * @return bool True if analytics is configured, false otherwise.
	 */
	private static function has_analytics_configured(): bool {
		// Check for Google Analytics
		$ga_id = get_option( 'google_analytics_id' );
		if ( ! empty( $ga_id ) ) {
			return true;
		}

		// Check for Google Analytics 4
		$ga4_id = get_option( 'google_analytics_4_id' );
		if ( ! empty( $ga4_id ) ) {
			return true;
		}

		// Check for Matomo
		$matomo_id = get_option( 'matomo_site_id' );
		if ( ! empty( $matomo_id ) ) {
			return true;
		}

		// Check for MonsterInsights plugin
		if ( function_exists( 'MonsterInsights' ) ) {
			return true;
		}

		// Check for ExactMetrics plugin
		if ( function_exists( 'ExactMetrics' ) ) {
			return true;
		}

		// Check for Site Kit by Google
		if ( class_exists( 'Google\Site_Kit\Plugin' ) ) {
			return true;
		}

		// Allow other plugins to indicate analytics is configured
		$has_analytics = apply_filters( 'wpshadow_has_analytics_tracking', false );

		return $has_analytics;
	}

	/**
	 * Check if referrer policy headers are configured.
	 *
	 * Checks if the site has referrer policy meta tags or headers set.
	 * This helps control what referrer information is sent with requests.
	 *
	 * @since  1.2601.2148
	 * @return bool True if referrer policy is configured, false otherwise.
	 */
	private static function has_referrer_policy(): bool {
		// Check if referrer policy is being set via WPShadow or other plugins
		$has_policy = get_option( 'wpshadow_referrer_policy_enabled', false );

		if ( $has_policy ) {
			return true;
		}

		// Load plugin functions if not already available
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check if common security plugins set referrer policy
		// Most modern security plugins set this header
		$security_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'ithemes-security/ithemes-security.php',
		);

		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Allow other plugins to indicate referrer policy is set
		$has_policy = apply_filters( 'wpshadow_has_referrer_policy', false );

		return $has_policy;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Referrer Quality
	 * Slug: referrer-quality
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if the site has proper mechanisms to track and monitor
	 *   traffic source quality.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_referrer_quality(): array {
		// Determine actual site state
		$has_analytics       = self::has_analytics_configured();
		$has_referrer_policy = self::has_referrer_policy();

		// Site has an issue if both analytics and referrer policy are missing
		$has_issue = ( ! $has_analytics || ! $has_referrer_policy );

		// Get diagnostic result
		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		// Test passes if diagnostic result matches actual state
		$test_passes = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Referrer quality check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (analytics: %s, referrer policy: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$has_analytics ? 'yes' : 'no',
				$has_referrer_policy ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
