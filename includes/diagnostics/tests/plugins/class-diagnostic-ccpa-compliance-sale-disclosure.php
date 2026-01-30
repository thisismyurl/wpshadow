<?php
/**
 * Ccpa Compliance Sale Disclosure Diagnostic
 *
 * Ccpa Compliance Sale Disclosure not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1134.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ccpa Compliance Sale Disclosure Diagnostic Class
 *
 * @since 1.1134.0000
 */
class Diagnostic_CcpaComplianceSaleDisclosure extends Diagnostic_Base {

	protected static $slug = 'ccpa-compliance-sale-disclosure';
	protected static $title = 'Ccpa Compliance Sale Disclosure';
	protected static $description = 'Ccpa Compliance Sale Disclosure not compliant';
	protected static $family = 'security';

	public static function check() {
		// Check for CCPA compliance plugins or e-commerce
		$has_ecommerce = class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' );
		$ccpa_plugin = defined( 'COOKIEYES_VERSION' ) || class_exists( 'GDPR_Cookie_Consent' );
		
		if ( ! $has_ecommerce && ! $ccpa_plugin ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Privacy policy page exists
		$privacy_page = get_option( 'wp_page_for_privacy_policy', 0 );
		if ( ! $privacy_page ) {
			$issues[] = __( 'No privacy policy page configured (CCPA requirement)', 'wpshadow' );
		}
		
		// Check 2: "Do Not Sell My Personal Information" link
		$dnsmpi_page = get_option( 'ccpa_do_not_sell_page', 0 );
		if ( ! $dnsmpi_page ) {
			$issues[] = __( '"Do Not Sell" page not configured (CCPA required)', 'wpshadow' );
		}
		
		// Check 3: Opt-out mechanism
		$opt_out_enabled = get_option( 'ccpa_opt_out_mechanism', false );
		if ( ! $opt_out_enabled ) {
			$issues[] = __( 'CCPA opt-out mechanism not implemented', 'wpshadow' );
		}
		
		// Check 4: Third-party data sales disclosure
		$third_party_sales = get_option( 'ccpa_disclose_third_party_sales', false );
		if ( ! $third_party_sales ) {
			$issues[] = __( 'Third-party data sales not disclosed in privacy policy', 'wpshadow' );
		}
		
		// Check 5: Categories of personal information
		$categories_listed = get_option( 'ccpa_list_data_categories', false );
		if ( ! $categories_listed ) {
			$issues[] = __( 'Personal information categories not listed (CCPA disclosure)', 'wpshadow' );
		}
		
		// Check 6: Consent record keeping
		$record_consent = get_option( 'ccpa_record_consent', false );
		if ( ! $record_consent ) {
			$issues[] = __( 'Consent records not maintained (compliance risk)', 'wpshadow' );
		}
		
		// Check 7: Annual privacy policy review
		if ( $privacy_page ) {
			$page = get_post( $privacy_page );
			$last_modified = strtotime( $page->post_modified );
			
			if ( ( time() - $last_modified ) > ( 365 * DAY_IN_SECONDS ) ) {
				$issues[] = __( 'Privacy policy not updated in over a year (CCPA recommends annual review)', 'wpshadow' );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 86;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 78;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of CCPA compliance issues */
				__( 'CCPA compliance has %d disclosure issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/ccpa-compliance-sale-disclosure',
		);
	}
}
