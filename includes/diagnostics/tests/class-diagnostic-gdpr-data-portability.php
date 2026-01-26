<?php
/**
 * GDPR Data Portability Diagnostic
 *
 * Verifies that WordPress has proper GDPR data portability features enabled,
 * allowing users to export their personal data as required by GDPR Article 20.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * GDPR Data Portability Diagnostic Class
 *
 * Checks if WordPress properly supports GDPR data portability requirements.
 * This includes verifying WordPress version, privacy policy configuration,
 * and that personal data exporters are registered.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Gdpr_Data_Portability extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-data-portability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Data Portability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies WordPress supports GDPR data portability, allowing users to export their personal data.';

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
	 * @since 1.2601.2148
	 * @return string The diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'gdpr-data-portability';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since 1.2601.2148
	 * @return string User-friendly diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Can users export their data?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since 1.2601.2148
	 * @return string Detailed description of what this diagnostic checks.
	 */
	public static function get_description(): string {
		return __( 'Verifies that WordPress has GDPR data portability features enabled, allowing users to export their personal data. Part of Compliance & Legal Risk analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since 1.2601.2148
	 * @return string The diagnostic category.
	 */
	public static function get_category(): string {
		return 'compliance_risk';
	}

	/**
	 * Run the diagnostic test
	 *
	 * Wrapper method that calls check() and formats the result for compatibility.
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
	 * @return int Threat level between 0 and 100.
	 */
	public static function get_threat_level(): int {
		// High threat level for GDPR compliance violations
		return 75;
	}

	/**
	 * Get KB article URL
	 *
	 * @since 1.2601.2148
	 * @return string URL to knowledge base article.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/gdpr-data-portability/';
	}

	/**
	 * Get training video URL
	 *
	 * @since 1.2601.2148
	 * @return string URL to training video.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/gdpr-data-portability/';
	}

	/**
	 * Check if GDPR data portability is properly supported.
	 *
	 * Verifies that WordPress has the necessary functionality to allow users
	 * to export their personal data, as required by GDPR Article 20 (Right to Data Portability).
	 *
	 * @since 1.2601.2148
	 * @return array|null Finding array if issue detected, null if all checks pass.
	 */
	public static function check(): ?array {
		// Check WordPress version for data export support (introduced in 4.9.6)
		global $wp_version;
		if ( version_compare( $wp_version, '4.9.6', '<' ) ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-portability',
				'Data Portability Not Supported',
				'WordPress version ' . $wp_version . ' does not support GDPR data portability. Update to WordPress 4.9.6 or higher to enable users to export their personal data.',
				'compliance',
				'critical',
				85,
				'gdpr-data-portability'
			);
		}

		// Check if privacy policy page is configured (required for data export UI)
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_policy_id ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-portability',
				'Privacy Policy Required for Data Exports',
				'No privacy policy page configured. WordPress requires a privacy policy page to display the data export functionality to users. Go to Settings → Privacy to configure one.',
				'compliance',
				'high',
				75,
				'gdpr-data-portability'
			);
		}

		// Verify the privacy policy page still exists and is published
		$privacy_page = get_post( $privacy_policy_id );
		if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-portability',
				'Privacy Policy Page Not Published',
				'The configured privacy policy page (ID: ' . $privacy_policy_id . ') is not published or has been deleted. Users cannot access data export features without a published privacy policy page.',
				'compliance',
				'high',
				75,
				'gdpr-data-portability'
			);
		}

		// Check if personal data exporters are registered
		// WordPress core registers default exporters, but plugins should add their own
		$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );
		if ( empty( $exporters ) ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-data-portability',
				'No Data Exporters Registered',
				'No personal data exporters are registered. While WordPress provides default exporters, they may not be loading correctly. Check for theme/plugin conflicts.',
				'compliance',
				'medium',
				60,
				'gdpr-data-portability'
			);
		}

		// All checks passed - data portability is properly supported
		return null;
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Validates that the GDPR data portability check works correctly
	 * based on the current WordPress site state.
	 *
	 * Diagnostic: Gdpr Data Portability
	 * Slug: gdpr-data-portability
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if WordPress has proper GDPR data portability support configured.
	 *
	 * @since 1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_gdpr_data_portability(): array {
		$result = self::check();

		global $wp_version;
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		$exporters         = apply_filters( 'wp_privacy_personal_data_exporters', array() );

		// Test logic: Determine if site should pass or fail
		$should_pass = version_compare( $wp_version, '4.9.6', '>=' )
			&& $privacy_policy_id > 0
			&& ! empty( $exporters );

		// Get privacy page status if it exists
		$privacy_page_published = false;
		if ( $privacy_policy_id > 0 ) {
			$privacy_page           = get_post( $privacy_policy_id );
			$privacy_page_published = ( $privacy_page && 'publish' === $privacy_page->post_status );
		}

		// Check if result matches expected state
		if ( $should_pass && $privacy_page_published ) {
			// Site should be healthy - check() should return null
			if ( null === $result ) {
				return array(
					'passed'  => true,
					'message' => 'PASS: Data portability is properly configured. WordPress ' . $wp_version . ', privacy policy ID ' . $privacy_policy_id . ' (published), ' . count( $exporters ) . ' exporters registered.',
				);
			} else {
				return array(
					'passed'  => false,
					'message' => 'FAIL: check() returned a finding when site appears healthy. WordPress ' . $wp_version . ', privacy policy configured. Finding: ' . ( isset( $result['title'] ) ? $result['title'] : 'Unknown' ),
				);
			}
		} else {
			// Site has issues - check() should return an array
			if ( is_array( $result ) && isset( $result['id'] ) ) {
				$reason = array();
				if ( version_compare( $wp_version, '4.9.6', '<' ) ) {
					$reason[] = 'WP version < 4.9.6';
				}
				if ( 0 === $privacy_policy_id ) {
					$reason[] = 'no privacy policy';
				}
				if ( $privacy_policy_id > 0 && ! $privacy_page_published ) {
					$reason[] = 'privacy policy not published';
				}
				if ( empty( $exporters ) ) {
					$reason[] = 'no exporters';
				}

				return array(
					'passed'  => true,
					'message' => 'PASS: check() correctly detected issue: ' . $result['title'] . ' (Reason: ' . implode( ', ', $reason ) . ')',
				);
			} else {
				return array(
					'passed'  => false,
					'message' => 'FAIL: check() should have returned a finding but returned null. Site has issues that should be detected.',
				);
			}
		}
	}
}
