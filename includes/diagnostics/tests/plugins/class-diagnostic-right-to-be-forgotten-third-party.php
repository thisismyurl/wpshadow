<?php
/**
 * Right To Be Forgotten Third Party Diagnostic
 *
 * Right To Be Forgotten Third Party not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1131.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Right To Be Forgotten Third Party Diagnostic Class
 *
 * @since 1.1131.0000
 */
class Diagnostic_RightToBeForgottenThirdParty extends Diagnostic_Base {

	protected static $slug = 'right-to-be-forgotten-third-party';
	protected static $title = 'Right To Be Forgotten Third Party';
	protected static $description = 'Right To Be Forgotten Third Party not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// WordPress core GDPR features present in 4.9.6+
		if ( ! function_exists( 'wp_privacy_generate_personal_data_export_file' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Third-party integrations detected
		$third_party_services = array();
		
		if ( defined( 'MAILCHIMP_VERSION' ) ) {
			$third_party_services[] = 'MailChimp';
		}
		if ( class_exists( 'Google\Analytics' ) || get_option( 'ga_id' ) ) {
			$third_party_services[] = 'Google Analytics';
		}
		if ( defined( 'STRIPE_VERSION' ) ) {
			$third_party_services[] = 'Stripe';
		}
		if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
			$third_party_services[] = 'WooCommerce';
		}
		
		if ( empty( $third_party_services ) ) {
			return null;
		}
		
		// Check 2: Third-party erasure configured
		$erasure_configured = get_option( 'wp_privacy_erase_third_party', false );
		if ( ! $erasure_configured ) {
			$issues[] = sprintf(
				/* translators: %s: list of third-party services */
				__( 'Third-party data erasure not configured for: %s', 'wpshadow' ),
				implode( ', ', $third_party_services )
			);
		}
		
		// Check 3: Manual deletion workflow
		$manual_workflow = get_option( 'wp_privacy_manual_third_party_workflow', false );
		if ( ! $manual_workflow && count( $third_party_services ) > 0 ) {
			$issues[] = __( 'No documented workflow for manual third-party data deletion', 'wpshadow' );
		}
		
		// Check 4: Data retention policies
		$retention_configured = get_option( 'wp_privacy_third_party_retention', false );
		if ( ! $retention_configured ) {
			$issues[] = __( 'Third-party data retention policies not documented', 'wpshadow' );
		}
		
		// Check 5: API integration for deletion
		if ( in_array( 'MailChimp', $third_party_services, true ) ) {
			$mailchimp_deletion = get_option( 'mailchimp_gdpr_delete_on_request', false );
			if ( ! $mailchimp_deletion ) {
				$issues[] = __( 'MailChimp automatic deletion not enabled', 'wpshadow' );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of GDPR compliance issues */
				__( 'Right to be forgotten has %d third-party compliance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/right-to-be-forgotten-third-party',
		);
	}
}
