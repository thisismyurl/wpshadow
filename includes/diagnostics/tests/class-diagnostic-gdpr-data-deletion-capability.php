<?php
/**
 * GDPR Data Deletion Capability Diagnostic
 *
 * Verifies that WordPress is properly configured to allow users to request
 * deletion of their personal data, fulfilling GDPR's "Right to Erasure" requirement.
 *
 * This diagnostic checks:
 * - WordPress version supports data deletion (4.9.6+)
 * - Privacy policy page is configured
 * - Privacy policy page is published and accessible
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic class for GDPR data deletion capability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Gdpr_Data_Deletion_Capability extends Diagnostic_Base {
	/**
	 * The diagnostic slug/ID
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $slug = 'gdpr-data-deletion-capability';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $title = 'Gdpr Data Deletion Capability';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $description = 'Verifies that WordPress is configured to allow users to request deletion of their personal data (GDPR Right to Erasure).';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $family = 'general';

	/**
	 * Display name for the family
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @since 1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'gdpr-data-deletion-capability';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since 1.2601.2148
	 * @return string Translatable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Can users request data deletion?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since 1.2601.2148
	 * @return string Translatable diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Can users request data deletion? Part of Compliance & Legal Risk analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since 1.2601.2148
	 * @return string Diagnostic category identifier.
	 */
	public static function get_category(): string {
		return 'compliance_risk';
	}

	/**
	 * Run the diagnostic test (legacy method)
	 *
	 * @since 1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$result = self::check();
		return is_array( $result ) ? $result : array();
	}

	/**
	 * Get threat level for this finding (0-100)
	 *
	 * @since 1.2601.2148
	 * @return int Threat level score.
	 */
	public static function get_threat_level(): int {
		return 57;
	}

	/**
	 * Get KB article URL
	 *
	 * @since 1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/gdpr-data-deletion-capability/';
	}

	/**
	 * Get training video URL
	 *
	 * @since 1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/gdpr-data-deletion-capability/';
	}

	/**
	 * Run the diagnostic check for GDPR data deletion capability.
	 *
	 * Verifies that WordPress has the necessary features configured to allow users
	 * to request deletion of their personal data (GDPR Right to Erasure).
	 *
	 * Checks:
	 * 1. WordPress version supports data deletion (4.9.6+ with wp_user_request function)
	 * 2. Privacy policy page is configured (required for data request forms)
	 * 3. Privacy policy page is published and accessible
	 *
	 * @since 1.2601.2148
	 * @return array|null Finding array if issues detected, null if compliant.
	 */
	public static function check(): ?array {
		// Check if WordPress version supports personal data deletion (introduced in 4.9.6).
		if ( ! function_exists( 'wp_user_request' ) ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-deletion-capability',
				'WordPress Data Deletion Not Supported',
				'WordPress 4.9.6+ is required for GDPR data deletion features. Update WordPress to enable users to request data deletion.',
				'compliance',
				'medium',
				50,
				'gdpr-data-deletion-capability'
			);
		}

		// Check if privacy policy page is configured.
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( 0 === $privacy_policy_id ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-deletion-capability',
				'Privacy Policy Not Configured',
				'A privacy policy page is required to enable user data deletion requests. Go to Settings → Privacy to configure one.',
				'compliance',
				'high',
				75,
				'gdpr-data-deletion-capability'
			);
		}

		// Check if the privacy policy page exists and is published.
		$privacy_page = get_post( $privacy_policy_id );
		if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-deletion-capability',
				'Privacy Policy Not Published',
				'The privacy policy page must be published for users to access data deletion request forms. Publish the page or assign a different one in Settings → Privacy.',
				'compliance',
				'high',
				70,
				'gdpr-data-deletion-capability'
			);
		}

		// All checks passed - data deletion capability is properly configured.
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Gdpr Data Deletion Capability
	 * Slug: gdpr-data-deletion-capability
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Verifies that WordPress has GDPR data deletion capability properly configured.
	 *
	 * @since 1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_gdpr_data_deletion_capability(): array {
		$result = self::check();

		// Determine expected state based on actual site configuration.
		$has_wp_support        = function_exists( 'wp_user_request' );
		$privacy_policy_id     = (int) get_option( 'wp_page_for_privacy_policy' );
		$has_privacy_policy    = 0 !== $privacy_policy_id;
		$privacy_page          = $has_privacy_policy ? get_post( $privacy_policy_id ) : null;
		$privacy_page_published = $privacy_page && 'publish' === $privacy_page->post_status;

		// Site is healthy if all conditions are met.
		$is_healthy = $has_wp_support && $has_privacy_policy && $privacy_page_published;

		// Test passes if result matches expected state.
		// Healthy site should return null, unhealthy should return finding array.
		if ( $is_healthy && null === $result ) {
			return array(
				'passed'  => true,
				'message' => 'Site is properly configured for GDPR data deletion. Check correctly returned null.',
			);
		} elseif ( ! $is_healthy && is_array( $result ) && isset( $result['id'] ) ) {
			return array(
				'passed'  => true,
				'message' => 'Site has data deletion issues. Check correctly identified: ' . $result['title'],
			);
		}

		// Result doesn't match expected state - test fails.
		if ( null === $result ) {
			return array(
				'passed'  => false,
				'message' => 'Check returned null but site has configuration issues (WP support: ' . ( $has_wp_support ? 'yes' : 'no' ) . ', Privacy policy: ' . ( $has_privacy_policy ? 'yes' : 'no' ) . ', Published: ' . ( $privacy_page_published ? 'yes' : 'no' ) . ')',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Check returned finding but site appears properly configured',
		);
	}
}
