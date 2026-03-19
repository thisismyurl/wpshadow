<?php
/**
 * GDPR Privacy Policy Diagnostic
 *
 * Checks if GDPR privacy policy is present and accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR Privacy Policy Diagnostic Class
 *
 * Verifies that a GDPR-compliant privacy policy is present and
 * that customer data handling is documented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_GDPR_Privacy_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-privacy-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Privacy Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if GDPR privacy policy is present and accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the GDPR privacy policy diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if GDPR issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for privacy policy page.
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
		$stats['privacy_policy_page_id'] = $privacy_page_id;

		if ( empty( $privacy_page_id ) ) {
			$issues[] = __( 'Privacy policy page not set', 'wpshadow' );
		} else {
			// Verify privacy page exists and is published.
			$privacy_page = get_post( $privacy_page_id );

			if ( ! $privacy_page || $privacy_page->post_status !== 'publish' ) {
				$issues[] = __( 'Privacy policy page not published or does not exist', 'wpshadow' );
			} else {
				$stats['privacy_policy_url'] = get_permalink( $privacy_page_id );
				$stats['privacy_policy_content_length'] = strlen( $privacy_page->post_content );

				if ( strlen( $privacy_page->post_content ) < 500 ) {
					$warnings[] = __( 'Privacy policy is very short - may lack required GDPR disclosures', 'wpshadow' );
				}
			}
		}

		// Check for GDPR required elements in privacy policy.
		if ( ! empty( $privacy_page_id ) ) {
			$privacy_page = get_post( $privacy_page_id );

			if ( $privacy_page ) {
				$content = strtolower( $privacy_page->post_content );

				$required_elements = array(
					'data collection' => false,
					'data retention' => false,
					'user rights' => false,
					'data deletion' => false,
					'cookies' => false,
					'consent' => false,
				);

				foreach ( array_keys( $required_elements ) as $element ) {
					if ( strpos( $content, $element ) !== false ) {
						$required_elements[ $element ] = true;
					}
				}

				$stats['gdpr_elements'] = $required_elements;
				$missing_elements = array_filter( $required_elements, function( $found ) {
					return ! $found;
				} );

				if ( count( $missing_elements ) > 0 ) {
					$warnings[] = sprintf(
						/* translators: %s: list of elements */
						__( 'Privacy policy missing GDPR elements: %s', 'wpshadow' ),
						implode( ', ', array_keys( $missing_elements ) )
					);
				}
			}
		}

		// Check for cookie consent.
		$cookie_consent_plugins = array(
			'cookie-law-info/cookie-law-info.php',
			'cookiebot/cookiebot.php',
			'iubenda-cookie-law-solution/iubenda.php',
		);

		$has_cookie_consent = false;
		foreach ( $cookie_consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cookie_consent = true;
				break;
			}
		}

		$stats['cookie_consent_enabled'] = $has_cookie_consent;

		if ( ! $has_cookie_consent ) {
			$warnings[] = __( 'No cookie consent plugin active - GDPR compliance risk', 'wpshadow' );
		}

		// Check for terms of service.
		$terms_page_id = get_option( 'woocommerce_terms_page_id' );
		$stats['terms_page_configured'] = ! empty( $terms_page_id );

		if ( empty( $terms_page_id ) ) {
			$warnings[] = __( 'Terms of Service page not configured', 'wpshadow' );
		}

		// Check for data request functionality.
		$data_request_page = get_option( 'wp_page_for_privacy_data_request' );
		$stats['data_request_page'] = ! empty( $data_request_page );

		if ( empty( $data_request_page ) ) {
			$warnings[] = __( 'Data request page not configured - required for GDPR subject access requests', 'wpshadow' );
		}

		// Check for GDPR plugins.
		$gdpr_plugins = array(
			'gdpr-framework/gdpr-framework.php',
			'wpems-gdpr-compliance/wpems-gdpr-compliance.php',
		);

		$has_gdpr_plugin = false;
		foreach ( $gdpr_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_gdpr_plugin = true;
				break;
			}
		}

		$stats['gdpr_plugin'] = $has_gdpr_plugin;

		// Check for data retention policy.
		$data_retention = get_option( 'woocommerce_customer_data_retention_days' );
		$stats['data_retention_days'] = ! empty( $data_retention ) ? intval( $data_retention ) : 'Not set';

		if ( empty( $data_retention ) ) {
			$warnings[] = __( 'Data retention policy not configured', 'wpshadow' );
		}

		// Check for email marketing consent.
		$email_consent_required = get_option( 'woocommerce_require_email_consent' );
		$stats['email_consent_required'] = boolval( $email_consent_required );

		if ( ! $email_consent_required ) {
			$warnings[] = __( 'Email marketing consent not required at checkout - GDPR issue', 'wpshadow' );
		}

		// Check for third-party data sharing disclosures.
		$sharing_disclosure = get_option( 'woocommerce_third_party_data_sharing' );
		$stats['sharing_disclosed'] = boolval( $sharing_disclosure );

		if ( ! $sharing_disclosure ) {
			$warnings[] = __( 'Third-party data sharing not disclosed', 'wpshadow' );
		}

		// Check for right to be forgotten implementation.
		$rtbf_enabled = get_option( 'woocommerce_right_to_be_forgotten' );
		$stats['right_to_be_forgotten'] = boolval( $rtbf_enabled );

		if ( ! $rtbf_enabled ) {
			$warnings[] = __( 'Right to be forgotten (data deletion) not fully implemented', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'GDPR privacy policy has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-privacy-policy',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'GDPR privacy policy has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-privacy-policy',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // GDPR privacy policy is compliant.
	}
}
