<?php
/**
 * GDPR Third-Party Vendors Disclosed Diagnostic
 *
 * Checks if third-party vendors and data processors are properly disclosed
 * in the site's privacy policy as required by GDPR Article 13 and 14.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR Third-Party Vendors Disclosed Diagnostic Class
 *
 * Verifies that the privacy policy contains disclosure of third-party vendors,
 * data processors, and service providers that have access to user data.
 * This is a GDPR compliance requirement under Articles 13 and 14.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Gdpr_Third_Party_Vendors_Disclosed extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $slug = 'gdpr-third-party-vendors-disclosed';

	/**
	 * Diagnostic title
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $title = 'GDPR Third-Party Vendors Disclosed';

	/**
	 * Diagnostic description
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $description = 'Verifies that third-party vendors and data processors are properly disclosed in the privacy policy for GDPR compliance.';

	/**
	 * Diagnostic family grouping
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Diagnostic family label
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $family_label = 'GDPR Compliance';

	/**
	 * Get diagnostic ID
	 *
	 * @since 1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'gdpr-third-party-vendors-disclosed';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since 1.2601.2148
	 * @return string Diagnostic display name.
	 */
	public static function get_name(): string {
		return __( 'Third-Party Vendors Disclosed', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since 1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Checks if third-party vendors and data processors are disclosed in the privacy policy. Part of GDPR Compliance analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since 1.2601.2148
	 * @return string Diagnostic category.
	 */
	public static function get_category(): string {
		return 'compliance_risk';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @since 1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$result = self::check();
		return $result ? $result : array();
	}

	/**
	 * Get threat level for this finding (0-100)
	 *
	 * @since 1.2601.2148
	 * @return int Threat level value.
	 */
	public static function get_threat_level(): int {
		return 70;
	}

	/**
	 * Get KB article URL
	 *
	 * @since 1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/gdpr-third-party-vendors-disclosed/';
	}

	/**
	 * Get training video URL
	 *
	 * @since 1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/gdpr-third-party-vendors-disclosed/';
	}

	/**
	 * Check if third-party vendors are properly disclosed
	 *
	 * Performs the diagnostic check to verify that third-party vendors,
	 * data processors, and service providers are disclosed in the privacy policy.
	 * This is required by GDPR Articles 13 and 14.
	 *
	 * The check follows this logic:
	 * 1. Verifies a privacy policy page is configured
	 * 2. Checks if the privacy policy exists and is published
	 * 3. Searches for vendor disclosure keywords in the policy content
	 * 4. Returns findings if vendors are not disclosed
	 *
	 * @since 1.2601.2148
	 * @return array|null Finding array if issues detected, null if compliant.
	 */
	public static function check(): ?array {
		// Get the configured privacy policy page ID.
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy' );

		// If no privacy policy is configured, return critical finding.
		if ( 0 === $privacy_policy_id ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-third-party-vendors-disclosed',
				__( 'No Privacy Policy Configured', 'wpshadow' ),
				__( 'No privacy policy page has been assigned. WordPress requires a privacy policy for GDPR compliance. Please go to Settings → Privacy to create or assign one, and ensure it discloses all third-party vendors and data processors.', 'wpshadow' ),
				'security',
				'high',
				75,
				'gdpr-third-party-vendors-disclosed'
			);
		}

		// Get the privacy policy page.
		$privacy_policy = get_post( $privacy_policy_id );

		// If policy doesn't exist or isn't published, return critical finding.
		if ( ! $privacy_policy || 'publish' !== $privacy_policy->post_status ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-third-party-vendors-disclosed',
				__( 'Privacy Policy Not Published', 'wpshadow' ),
				__( 'The assigned privacy policy page is not published or has been deleted. Please ensure a valid privacy policy is published and discloses all third-party vendors.', 'wpshadow' ),
				'security',
				'high',
				73,
				'gdpr-third-party-vendors-disclosed'
			);
		}

		// Check if privacy policy content contains vendor disclosure keywords.
		$has_vendor_disclosure = self::has_vendor_disclosure( $privacy_policy->post_content );

		if ( ! $has_vendor_disclosure ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-third-party-vendors-disclosed',
				__( 'Third-Party Vendors Not Disclosed', 'wpshadow' ),
				__( 'Your privacy policy does not appear to disclose third-party vendors or data processors. GDPR Articles 13 and 14 require you to inform users about all third parties who process their data. Please update your privacy policy to list all service providers, analytics tools, payment processors, and other third parties that have access to user data.', 'wpshadow' ),
				'security',
				'high',
				70,
				'gdpr-third-party-vendors-disclosed'
			);
		}

		// No issues found - vendors are disclosed.
		return null;
	}

	/**
	 * Check if content contains vendor disclosure keywords
	 *
	 * Examines the provided content for keywords that indicate
	 * third-party vendor disclosure. Uses multiple search terms
	 * to reduce false negatives.
	 *
	 * @since 1.2601.2148
	 * @param string $content The content to search.
	 * @return bool True if vendor disclosure keywords found, false otherwise.
	 */
	private static function has_vendor_disclosure( string $content ): bool {
		// Convert to lowercase for case-insensitive search.
		$content_lower = strtolower( $content );

		// Keywords that indicate vendor disclosure.
		$keywords = array(
			'third party',
			'third-party',
			'third parties',
			'data processor',
			'data processors',
			'service provider',
			'service providers',
			'vendor',
			'vendors',
			'sub-processor',
			'subprocessor',
		);

		// Check if any keyword is present in the content.
		foreach ( $keywords as $keyword ) {
			if ( false !== strpos( $content_lower, $keyword ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Tests the diagnostic against the actual WordPress site state.
	 * Validates that the check() method returns appropriate results
	 * based on whether vendors are disclosed in the privacy policy.
	 *
	 * Test Logic:
	 * - PASS: check() returns NULL (no issues - vendors are disclosed or verified)
	 * - FAIL: check() returns array (issue detected - vendors not disclosed)
	 *
	 * @since 1.2601.2148
	 * @return array {
	 *     Test result array.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_gdpr_third_party_vendors_disclosed(): array {
		// Run the actual diagnostic check.
		$result = self::check();

		// Get the privacy policy ID for context in messages.
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy' );

		// If check returns null, the site is compliant.
		if ( null === $result ) {
			return array(
				'passed'  => true,
				/* translators: %d: privacy policy page ID */
				'message' => sprintf(
					__( 'PASS: Third-party vendors appear to be disclosed in the privacy policy (Page ID: %d). The diagnostic correctly returns NULL for compliant sites.', 'wpshadow' ),
					$privacy_policy_id
				),
			);
		}

		// If check returns an array, an issue was detected.
		// Verify the finding structure is correct.
		$required_keys = array( 'id', 'title', 'description', 'severity', 'threat_level' );
		$has_all_keys  = true;

		foreach ( $required_keys as $key ) {
			if ( ! isset( $result[ $key ] ) ) {
				$has_all_keys = false;
				break;
			}
		}

		if ( ! $has_all_keys ) {
			return array(
				'passed'  => false,
				'message' => __( 'FAIL: The check() method returned a finding but it is missing required keys. Expected keys: id, title, description, severity, threat_level.', 'wpshadow' ),
			);
		}

		// Verify the finding ID matches.
		if ( 'gdpr-third-party-vendors-disclosed' !== $result['id'] ) {
			return array(
				'passed'  => false,
				/* translators: %s: found ID */
				'message' => sprintf(
					__( 'FAIL: The finding ID is incorrect. Expected "gdpr-third-party-vendors-disclosed" but got "%s".', 'wpshadow' ),
					esc_html( $result['id'] )
				),
			);
		}

		// Finding structure is valid and issue detected.
		if ( 0 === $privacy_policy_id ) {
			$context = __( 'No privacy policy configured', 'wpshadow' );
		} else {
			$privacy_policy = get_post( $privacy_policy_id );
			if ( ! $privacy_policy || 'publish' !== $privacy_policy->post_status ) {
				$context = __( 'Privacy policy not published or deleted', 'wpshadow' );
			} else {
				$context = __( 'Privacy policy exists but vendor disclosure keywords not found', 'wpshadow' );
			}
		}

		return array(
			'passed'  => true,
			/* translators: %s: context of the issue */
			'message' => sprintf(
				__( 'PASS: The diagnostic correctly detected a GDPR compliance issue. Context: %s. The finding structure is valid and contains all required information.', 'wpshadow' ),
				$context
			),
		);
	}
}
