<?php
/**
 * GDPR Data Retention Policy Diagnostic
 *
 * Verifies that a data retention policy is properly documented in the
 * site's privacy policy, as required by GDPR Article 13(2)(a).
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
 * GDPR Data Retention Policy Diagnostic Class
 *
 * Checks whether the site has documented a data retention policy in its
 * privacy policy page. Under GDPR Article 13(2)(a), organizations must
 * inform users about the period for which personal data will be stored,
 * or the criteria used to determine that period.
 *
 * This diagnostic performs a content analysis of the privacy policy to
 * detect common retention-related keywords and phrases.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Gdpr_Data_Retention_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug/ID
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-data-retention-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Data Retention Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that a data retention policy is documented in the privacy policy as required by GDPR Article 13(2)(a).';

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
	 */
	public static function get_id(): string {
		return 'gdpr-data-retention-policy';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is data retention policy documented?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is data retention policy documented?. Part of Compliance & Legal Risk analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'compliance_risk';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// Implement: Is data retention policy documented? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 49;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/gdpr-data-retention-policy/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/gdpr-data-retention-policy/';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if the site has a documented data retention policy in its privacy policy.
	 * GDPR Article 13(2)(a) requires organizations to inform users about how long their
	 * personal data will be stored.
	 *
	 * Detection Logic:
	 * 1. Verifies a privacy policy page is configured in WordPress
	 * 2. Checks if the privacy policy page exists and is published
	 * 3. Scans privacy policy content for retention-related keywords
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null if no issues found.
	 */
	public static function check(): ?array {
		// Check if privacy policy page is configured.
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( 0 === $privacy_policy_id ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-retention-policy',
				__( 'No Privacy Policy Configured', 'wpshadow' ),
				__( 'You don\'t have a privacy policy page set up yet. GDPR requires documenting how long you keep user data. Go to Settings → Privacy to create or assign your privacy policy page, then add a section explaining your data retention practices.', 'wpshadow' ),
				'compliance',
				'high',
				75,
				'gdpr-data-retention-policy'
			);
		}

		// Check if the privacy policy page exists and is published.
		$privacy_policy = get_post( $privacy_policy_id );
		if ( ! $privacy_policy || 'publish' !== $privacy_policy->post_status ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-retention-policy',
				__( 'Privacy Policy Page Not Available', 'wpshadow' ),
				__( 'Your privacy policy page is either deleted or not published. GDPR compliance requires a publicly accessible privacy policy with data retention information. Please publish or create a new privacy policy page.', 'wpshadow' ),
				'compliance',
				'high',
				80,
				'gdpr-data-retention-policy'
			);
		}

		// Analyze privacy policy content for retention-related information.
		$content = strtolower( $privacy_policy->post_content );

		// Keywords that indicate retention policy documentation.
		$retention_keywords = array(
			'retention',
			'retain',
			'keep',
			'store',
			'delete',
			'deletion',
			'remove',
			'days',
			'months',
			'years',
			'period',
			'duration',
			'expir',
		);

		// Check if any retention keywords are present.
		$has_retention_info = false;
		foreach ( $retention_keywords as $keyword ) {
			if ( false !== strpos( $content, $keyword ) ) {
				$has_retention_info = true;
				break;
			}
		}

		if ( ! $has_retention_info ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-retention-policy',
				__( 'Data Retention Policy Missing', 'wpshadow' ),
				sprintf(
					/* translators: %s: link to privacy policy page */
					__( 'Your privacy policy doesn\'t mention how long you keep user data. GDPR Article 13(2)(a) requires this information. Update your <a href="%s" target="_blank">privacy policy</a> to include details like "We retain user data for 12 months after account deletion" or "Contact information is kept as long as your account is active." [<a href="https://wpshadow.com/kb/gdpr-data-retention-policy/" target="_blank">Learn more</a>]', 'wpshadow' ),
					esc_url( get_edit_post_link( $privacy_policy_id ) )
				),
				'compliance',
				'medium',
				65,
				'gdpr-data-retention-policy'
			);
		}

		// Privacy policy exists and contains retention information.
		return null;
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Validates that the check() method returns the correct result based on
	 * the actual WordPress site state. This test helps ensure the diagnostic
	 * is working correctly in production.
	 *
	 * Test Logic:
	 * - If no privacy policy is configured: FAIL (finding expected)
	 * - If privacy policy exists but no retention info: FAIL (finding expected)
	 * - If privacy policy exists with retention info: PASS (no finding)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result information.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_gdpr_data_retention_policy(): array {
		$result = self::check();

		// Get privacy policy status for detailed test message.
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( 0 === $privacy_policy_id ) {
			// No privacy policy configured - finding expected.
			if ( null !== $result ) {
				return array(
					'passed'  => true,
					'message' => __( 'Test PASSED: Correctly detected missing privacy policy configuration.', 'wpshadow' ),
				);
			} else {
				return array(
					'passed'  => false,
					'message' => __( 'Test FAILED: Should detect missing privacy policy but returned no finding.', 'wpshadow' ),
				);
			}
		}

		$privacy_policy = get_post( $privacy_policy_id );
		if ( ! $privacy_policy || 'publish' !== $privacy_policy->post_status ) {
			// Privacy policy not published - finding expected.
			if ( null !== $result ) {
				return array(
					'passed'  => true,
					'message' => __( 'Test PASSED: Correctly detected unpublished or missing privacy policy.', 'wpshadow' ),
				);
			} else {
				return array(
					'passed'  => false,
					'message' => __( 'Test FAILED: Should detect unpublished privacy policy but returned no finding.', 'wpshadow' ),
				);
			}
		}

		// Privacy policy exists, check content.
		$content            = strtolower( $privacy_policy->post_content );
		$retention_keywords = array(
			'retention',
			'retain',
			'keep',
			'store',
			'delete',
			'deletion',
			'remove',
			'days',
			'months',
			'years',
			'period',
			'duration',
			'expir',
		);

		$has_retention_info = false;
		foreach ( $retention_keywords as $keyword ) {
			if ( false !== strpos( $content, $keyword ) ) {
				$has_retention_info = true;
				break;
			}
		}

		if ( $has_retention_info ) {
			// Has retention info - no finding expected.
			if ( null === $result ) {
				return array(
					'passed'  => true,
					'message' => __( 'Test PASSED: Privacy policy contains retention information, no finding returned.', 'wpshadow' ),
				);
			} else {
				return array(
					'passed'  => false,
					'message' => __( 'Test FAILED: Privacy policy contains retention keywords but diagnostic returned a finding.', 'wpshadow' ),
				);
			}
		} elseif ( null !== $result ) {
			// No retention info - finding expected.
			return array(
				'passed'  => true,
				'message' => __( 'Test PASSED: Correctly detected missing retention policy in privacy policy content.', 'wpshadow' ),
			);
		} else {
			return array(
				'passed'  => false,
				'message' => __( 'Test FAILED: Should detect missing retention policy but returned no finding.', 'wpshadow' ),
			);
		}
	}
}
