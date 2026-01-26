<?php
/**
 * GDPR Cookies Disclosed Diagnostic
 *
 * Checks whether the website properly discloses cookie usage to visitors,
 * as required by GDPR (General Data Protection Regulation) and similar
 * privacy laws like CCPA, PECR, and ePrivacy Directive.
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
 * Diagnostic_Gdpr_Cookies_Disclosed Class
 *
 * Verifies that cookie usage is properly disclosed through either:
 * 1. Active cookie consent/notice plugin
 * 2. Cookie information in the privacy policy
 *
 * Under GDPR Article 13, websites must inform users about cookies
 * before setting non-essential cookies. Failure to disclose can result
 * in fines up to €20 million or 4% of annual global turnover.
 */
class Diagnostic_Gdpr_Cookies_Disclosed extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-cookies-disclosed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Cookies Disclosed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that cookie usage is properly disclosed to visitors as required by GDPR and privacy laws.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Display name for the family
	 *
	 * @var string
	 */
	protected static $family_label = 'Compliance';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'gdpr-cookies-disclosed';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Cookie Usage Disclosed', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Checks if cookie usage is disclosed. Part of GDPR compliance and legal risk analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'compliance_risk';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @since  1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$result = self::check();
		return $result ? $result : array();
	}

	/**
	 * Get threat level for this finding (0-100)
	 *
	 * High severity (75) because:
	 * - GDPR violations can result in fines up to €20M or 4% of global turnover
	 * - Required by law in EU, UK, and affects sites with EU visitors
	 * - Non-compliance creates legal liability
	 *
	 * @since  1.2601.2148
	 * @return int Threat level from 0-100.
	 */
	public static function get_threat_level(): int {
		return 75;
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/gdpr-cookies-disclosed/';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/gdpr-cookies-disclosed/';
	}

	/**
	 * Check if the site has cookie disclosure
	 *
	 * Helper method to determine if cookie usage is disclosed through
	 * either an active cookie consent plugin or privacy policy content.
	 *
	 * @since  1.2601.2148
	 * @return bool True if disclosure is present, false otherwise.
	 */
	private static function has_cookie_disclosure(): bool {
		// Check if popular cookie consent plugins are active.
		// Early return if any plugin is found to avoid database queries.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$consent_plugins = array(
			'cookie-notice/cookie-notice.php',               // Cookie Notice & Compliance.
			'iubenda-cookie-law-consent/iubenda.php',        // Iubenda Cookie Solution.
			'termly-cookie-consent/termly.php',              // Termly Cookie Consent.
			'cookiebot/cookiebot.php',                       // Cookiebot CMP.
			'gdpr-cookie-compliance/moove-gdpr.php',         // GDPR Cookie Compliance.
			'cookie-law-info/cookie-law-info.php',           // CookieYes.
			'complianz-gdpr/complianz-gdpr.php',             // Complianz.
			'uk-cookie-consent/uk-cookie-consent.php',       // UK Cookie Consent.
			'wp-gdpr-compliance/wp-gdpr-compliance.php',     // WP GDPR Compliance.
		);

		// Check if any cookie consent plugin is active (fastest check).
		foreach ( $consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Fallback: Check privacy policy for cookie disclosure (database query).
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );

		if ( $privacy_policy_id <= 0 ) {
			return false;
		}

		$privacy_policy = get_post( $privacy_policy_id );

		// Check if privacy policy exists, is published, and mentions cookies.
		if ( ! $privacy_policy || 'publish' !== $privacy_policy->post_status ) {
			return false;
		}

		// Search for cookie-related keywords in the content.
		$content         = $privacy_policy->post_content;
		$cookie_keywords = array( 'cookie', 'cookies', 'tracking', 'analytics' );

		foreach ( $cookie_keywords as $keyword ) {
			if ( false !== stripos( $content, $keyword ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Run the diagnostic check
	 *
	 * Checks if cookie usage is properly disclosed through:
	 * 1. Popular cookie consent/notice plugins being active
	 * 2. Cookie disclosure in the privacy policy content
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check(): ?array {
		// Check if disclosure is present using helper method.
		if ( self::has_cookie_disclosure() ) {
			return null;
		}

		// If no disclosure found, return a finding.
		return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
			'gdpr-cookies-disclosed',
			'Cookie Usage Not Disclosed',
			__( 'Your website does not appear to disclose cookie usage. GDPR Article 13 requires websites to inform visitors about cookies before setting non-essential ones. Install a cookie consent plugin or add cookie information to your privacy policy.', 'wpshadow' ),
			'compliance',
			'high',
			75,
			'gdpr-cookies-disclosed'
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Validates that the check() method returns appropriate results
	 * based on the current WordPress site state.
	 *
	 * Test Logic:
	 * - If cookie disclosure is present (plugin active or privacy policy mentions cookies):
	 *   check() should return NULL (no issue = PASS)
	 * - If no cookie disclosure is present:
	 *   check() should return array (issue found = PASS)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_gdpr_cookies_disclosed(): array {
		// Run the actual diagnostic check.
		$result = self::check();

		// Determine if the site has cookie disclosure using helper method.
		$has_disclosure = self::has_cookie_disclosure();

		// Validate the check() result matches the expected behavior.
		if ( $has_disclosure ) {
			// Site has disclosure, check() should return null.
			if ( null === $result ) {
				return array(
					'passed'  => true,
					'message' => __( 'Test passed: Cookie disclosure found, check() correctly returned null (no issue).', 'wpshadow' ),
				);
			} else {
				return array(
					'passed'  => false,
					'message' => __( 'Test failed: Cookie disclosure found but check() returned an issue (false positive).', 'wpshadow' ),
				);
			}
		} else {
			// Site has no disclosure, check() should return an array.
			if ( is_array( $result ) && ! empty( $result ) ) {
				return array(
					'passed'  => true,
					'message' => __( 'Test passed: No cookie disclosure found, check() correctly returned an issue.', 'wpshadow' ),
				);
			} else {
				return array(
					'passed'  => false,
					'message' => __( 'Test failed: No cookie disclosure found but check() did not return an issue (false negative).', 'wpshadow' ),
				);
			}
		}
	}
}
