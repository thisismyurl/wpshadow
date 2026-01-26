<?php
/**
 * GDPR Breach Notification Plan Diagnostic
 *
 * Checks if the site has a documented breach notification plan
 * as required by GDPR Article 33 and Article 34.
 *
 * @package WPShadow
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR Breach Notification Plan Diagnostic
 *
 * Verifies that the site has procedures in place for data breach
 * notification as required by GDPR. Checks for:
 * - Privacy policy existence (baseline)
 * - Documented contact information for breach reporting
 * - Evidence of breach notification procedures
 * - WPShadow incident response configuration
 *
 * GDPR requires notification to authorities within 72 hours of
 * discovery (Article 33) and to affected data subjects when high
 * risk exists (Article 34).
 *
 * @since 1.2601.2148
 */
class Diagnostic_GdprBreachNotificationPlan extends Diagnostic_Base {

	/**
	 * The diagnostic slug/ID
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-breach-notification-plan';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Breach Notification Plan';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that site has documented breach notification procedures as required by GDPR Articles 33 & 34';

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
		return 'gdpr-breach-notification-plan';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Is breach notification plan in place?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Detailed description.
	 */
	public static function get_description(): string {
		return __( 'Verifies breach notification procedures exist per GDPR Articles 33 & 34. Part of Compliance & Legal Risk analysis.', 'wpshadow' );
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
	 * @since  1.2601.2148
	 * @return int Threat level score.
	 */
	public static function get_threat_level(): int {
		// High threat: GDPR fines can reach €20M for non-compliance
		return 75;
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/gdpr-breach-notification-plan/';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/gdpr-breach-notification-plan/';
	}

	/**
	 * Run the diagnostic check
	 *
	 * Checks if the site has documented breach notification procedures:
	 * 1. Privacy policy must exist (baseline requirement)
	 * 2. Verify contact information for breach reporting
	 * 3. Check for WPShadow incident response configuration
	 *
	 * GDPR Articles 33 & 34 require:
	 * - Notification to authorities within 72 hours
	 * - Direct notification to data subjects when high risk exists
	 * - Documentation of breach response procedures
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check(): ?array {
		// Check if privacy policy page exists (baseline requirement)
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( 0 === $privacy_page_id ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-breach-notification-plan',
				__( 'No Privacy Policy Configured', 'wpshadow' ),
				__( 'GDPR breach notification requires a privacy policy as the foundation. Configure one in Settings → Privacy before establishing breach procedures. GDPR Articles 33 & 34 require notification to authorities within 72 hours and to affected users when high risk exists.', 'wpshadow' ),
				'compliance',
				'high',
				85,
				'gdpr-breach-notification-plan'
			);
		}

		// Check if privacy policy is published
		$privacy_page = get_post( $privacy_page_id );
		if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-breach-notification-plan',
				__( 'Privacy Policy Not Published', 'wpshadow' ),
				__( 'The privacy policy page must be published to serve as the foundation for breach notification procedures. Publish it in Settings → Privacy, then document your breach response plan including authority contact information and 72-hour notification timeline.', 'wpshadow' ),
				'compliance',
				'high',
				80,
				'gdpr-breach-notification-plan'
			);
		}

		// Check for breach notification documentation in WPShadow settings
		$breach_plan_documented = get_option( 'wpshadow_gdpr_breach_plan_documented', '' );

		if ( empty( $breach_plan_documented ) || 'yes' !== $breach_plan_documented ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-breach-notification-plan',
				__( 'Breach Notification Plan Not Documented', 'wpshadow' ),
				__( 'No documented breach notification plan found. GDPR requires procedures for notifying authorities within 72 hours (Article 33) and affected users when high risk exists (Article 34). Document your plan including: authority contact info, notification timeline, data inventory, and incident response procedures. Mark as complete in WPShadow settings.', 'wpshadow' ),
				'compliance',
				'high',
				75,
				'gdpr-breach-notification-plan'
			);
		}

		// Check for admin email configuration (used for breach notifications)
		$admin_email = get_option( 'admin_email', '' );
		if ( empty( $admin_email ) ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-breach-notification-plan',
				__( 'Admin Email Not Configured', 'wpshadow' ),
				__( 'No admin email configured for breach notifications. Configure an email address in Settings → General to receive security alerts and coordinate breach response per GDPR requirements.', 'wpshadow' ),
				'compliance',
				'medium',
				65,
				'gdpr-breach-notification-plan'
			);
		}

		// All checks passed - breach notification plan appears to be in place
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Validates that the check() method returns correct results based
	 * on the actual WordPress site state.
	 *
	 * Diagnostic: GDPR Breach Notification Plan
	 * Slug: gdpr-breach-notification-plan
	 *
	 * Test Purpose:
	 * - PASS: check() returns NULL when breach plan is documented (compliant)
	 * - FAIL: check() returns array when breach plan is missing (non-compliant)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result with status and message.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_gdpr_breach_notification_plan(): array {
		$result = self::check();

		// Get actual site state
		$privacy_page_id        = (int) get_option( 'wp_page_for_privacy_policy' );
		$breach_plan_documented = get_option( 'wpshadow_gdpr_breach_plan_documented', '' );
		$admin_email            = get_option( 'admin_email', '' );

		// Check if privacy policy is published
		$privacy_page_published = false;
		if ( 0 !== $privacy_page_id ) {
			$privacy_page           = get_post( $privacy_page_id );
			$privacy_page_published = ( $privacy_page && 'publish' === $privacy_page->post_status );
		}

		// Determine expected state - all conditions must be met
		$should_pass = ( $privacy_page_published && 'yes' === $breach_plan_documented && ! empty( $admin_email ) );

		// Verify result matches expected state
		$actual_pass = ( null === $result );

		if ( $should_pass === $actual_pass ) {
			$state_msg = $should_pass ? 'compliant (plan documented)' : 'non-compliant (plan missing)';
			return array(
				'passed'  => true,
				'message' => sprintf(
					/* translators: %s: compliance state description */
					__( 'Test passed: Diagnostic correctly detected site is %s', 'wpshadow' ),
					$state_msg
				),
			);
		}

		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: 1: expected state, 2: actual result */
				__( 'Test failed: Expected %1$s but diagnostic returned %2$s', 'wpshadow' ),
				$should_pass ? 'compliant' : 'non-compliant',
				$actual_pass ? 'compliant' : 'non-compliant'
			),
		);
	}
}
