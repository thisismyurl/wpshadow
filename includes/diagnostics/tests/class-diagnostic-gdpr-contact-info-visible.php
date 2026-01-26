<?php
/**
 * GDPR Contact Information Visibility Diagnostic
 *
 * Checks if required contact information for GDPR compliance is visible on the site.
 * GDPR Articles 13 and 14 require organizations to provide:
 * - Identity and contact details of the data controller
 * - Contact details of the Data Protection Officer (DPO) where applicable
 * - Methods for data subjects to exercise their rights
 *
 * This diagnostic validates that contact information is accessible through
 * the privacy policy page and that admin email is properly configured.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Gdpr_Contact_Info_Visible extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-contact-info-visible';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Contact Info Visible';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that required contact information for GDPR compliance is visible on the site through privacy policy and admin settings.';

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
	protected static $family_label = 'Compliance & Legal';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string The unique identifier for this diagnostic.
	 */
	public static function get_id(): string {
		return 'gdpr-contact-info-visible';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Translated diagnostic name for display.
	 */
	public static function get_name(): string {
		return __( 'Is contact/DPA info on site?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Translated diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Checks if contact and Data Protection Officer information is visible as required by GDPR. Part of Compliance & Legal Risk analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string The category this diagnostic belongs to.
	 */
	public static function get_category(): string {
		return 'compliance_risk';
	}

	/**
	 * Run the diagnostic test
	 *
	 * Legacy method for backwards compatibility.
	 * Use check() method instead for new implementations.
	 *
	 * @since  1.2601.2148
	 * @return array Finding data or empty if no issue found.
	 */
	public static function run(): array {
		$result = self::check();
		return null === $result ? array() : $result;
	}

	/**
	 * Get threat level for this finding (0-100)
	 *
	 * GDPR compliance issues can result in significant fines (up to 4% of annual revenue).
	 * Contact information visibility is a fundamental requirement.
	 *
	 * @since  1.2601.2148
	 * @return int Threat level from 0-100, where higher values indicate greater risk.
	 */
	public static function get_threat_level(): int {
		return 70;
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string URL to knowledge base article with detailed guidance.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/gdpr-contact-info-visible/';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string URL to training video explaining GDPR contact requirements.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/gdpr-contact-info-visible/';
	}

	/**
	 * Check if GDPR-required contact information is visible on the site.
	 *
	 * Performs comprehensive validation of contact information visibility:
	 * 1. Verifies privacy policy page exists and is configured
	 * 2. Checks if privacy policy contains contact information
	 * 3. Looks for DPO (Data Protection Officer) references
	 * 4. Validates admin email is properly configured
	 *
	 * GDPR Requirements (Articles 13 & 14):
	 * - Identity and contact details of the controller
	 * - Contact details of the DPO (where applicable)
	 * - Methods for exercising data subject rights
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Finding array if issue detected, null if site is compliant.
	 *
	 *     @type string $id            Diagnostic identifier.
	 *     @type string $title         Human-readable title.
	 *     @type string $description   Detailed description of the issue.
	 *     @type string $category      Category (e.g., 'security').
	 *     @type string $severity      Severity level: 'low', 'medium', 'high', or 'critical'.
	 *     @type int    $threat_level  Numeric threat level (0-100).
	 *     @type string $kb_link       URL to knowledge base article.
	 *     @type string $training_link URL to training video.
	 *     @type bool   $auto_fixable  Whether this can be auto-fixed (always false).
	 * }
	 */
	public static function check(): ?array {
		// Check if contact information is visible (required for GDPR Article 13 & 14).
		// GDPR requires organizations to provide identity and contact details of the controller,
		// and contact details of the Data Protection Officer (DPO) where applicable.

		// Step 1: Check if privacy policy page exists (primary location for contact info).
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( ! $privacy_policy_id ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-contact-info-visible',
				'GDPR Contact Info Visible',
				'No privacy policy page configured. GDPR requires visible contact details of the data controller and DPO for data subject requests.',
				'security',
				'high',
				75,
				'gdpr-contact-info-visible'
			);
		}

		// Step 2: Check if privacy policy has contact/DPA information.
		$privacy_policy = get_post( $privacy_policy_id );
		if ( ! $privacy_policy || empty( $privacy_policy->post_content ) ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-contact-info-visible',
				'GDPR Contact Info Visible',
				'Privacy policy exists but is empty. Add contact details of the data controller and DPO as required by GDPR.',
				'security',
				'high',
				70,
				'gdpr-contact-info-visible'
			);
		}

		// Step 3: Check if privacy policy contains contact information keywords.
		$content = strtolower( $privacy_policy->post_content );

		// Look for contact-related terms.
		$has_contact_keywords = strpos( $content, 'contact' ) !== false ||
								strpos( $content, 'email' ) !== false ||
								strpos( $content, 'phone' ) !== false ||
								strpos( $content, 'address' ) !== false ||
								strpos( $content, 'mail' ) !== false;

		// Look for DPO/controller-related terms.
		$has_dpo_keywords = strpos( $content, 'data protection officer' ) !== false ||
							strpos( $content, 'dpo' ) !== false ||
							strpos( $content, 'controller' ) !== false ||
							strpos( $content, 'data controller' ) !== false;

		if ( ! $has_contact_keywords && ! $has_dpo_keywords ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-contact-info-visible',
				'GDPR Contact Info Visible',
				'Privacy policy lacks contact information. GDPR requires clear contact details for the data controller and DPO to handle data subject requests.',
				'security',
				'high',
				65,
				'gdpr-contact-info-visible'
			);
		}

		// Step 4: Additional check - verify admin email is set and not a default/test value.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || strpos( $admin_email, 'example.com' ) !== false || strpos( $admin_email, 'test' ) !== false ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-contact-info-visible',
				'GDPR Contact Info Visible',
				'Admin email is not properly configured. Set a valid contact email for GDPR compliance.',
				'security',
				'medium',
				55,
				'gdpr-contact-info-visible'
			);
		}

		// All checks passed - contact info appears to be visible.
		return null;
	}

	/**
	 * Live test for GDPR Contact Info Visible diagnostic
	 *
	 * Validates that the check() method correctly identifies GDPR contact
	 * information visibility issues on the actual WordPress site.
	 *
	 * Test Logic:
	 * - Calls check() to get the diagnostic result
	 * - Validates finding structure if an issue is detected
	 * - Verifies the result matches the actual site state
	 * - Returns pass/fail with descriptive message
	 *
	 * Test Criteria:
	 * - PASS: check() returns NULL when contact info is visible (site is healthy)
	 * - PASS: check() returns valid finding when contact info is missing (issue found)
	 * - FAIL: check() returns inconsistent or invalid result
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result with status and message.
	 *
	 *     @type bool   $passed  Whether the test passed validation.
	 *     @type string $message Human-readable test result message with context.
	 * }
	 */
	public static function test_live_gdpr_contact_info_visible(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		$result = self::check();

		// Gather diagnostic state for validation.
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy' );
		$admin_email       = get_option( 'admin_email' );

		// Test passes if no finding is returned (site is healthy).
		if ( null === $result ) {
			// Verify that the passing state makes sense.
			if ( ! $privacy_policy_id ) {
				return array(
					'passed'  => false,
					'message' => 'Test FAILED: check() returned null but no privacy policy is configured.',
				);
			}

			$privacy_policy = get_post( $privacy_policy_id );
			if ( ! $privacy_policy || empty( $privacy_policy->post_content ) ) {
				return array(
					'passed'  => false,
					'message' => 'Test FAILED: check() returned null but privacy policy is empty.',
				);
			}

			return array(
				'passed'  => true,
				'message' => 'Test PASSED: Contact/DPA information appears to be visible. Privacy policy configured with contact details.',
			);
		}

		// Test passes if a finding is returned (issue detected).
		// Verify the finding is valid.
		if ( ! is_array( $result ) || ! isset( $result['id'] ) || 'gdpr-contact-info-visible' !== $result['id'] ) {
			return array(
				'passed'  => false,
				'message' => 'Test FAILED: check() returned invalid finding structure.',
			);
		}

		// Verify the finding severity is appropriate.
		if ( ! isset( $result['severity'] ) || ! in_array( $result['severity'], array( 'low', 'medium', 'high', 'critical' ), true ) ) {
			return array(
				'passed'  => false,
				'message' => 'Test FAILED: Finding has invalid severity level.',
			);
		}

		// All validation passed.
		return array(
			'passed'  => true,
			'message' => sprintf(
				'Test PASSED: Issue detected correctly. Severity: %s, Threat: %d. %s',
				$result['severity'],
				$result['threat_level'],
				$result['description']
			),
		);
	}
}
