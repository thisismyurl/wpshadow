<?php
/**
 * Formidable Forms Payment Diagnostic
 *
 * Formidable Forms payment security weak.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.264.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms Payment Diagnostic Class
 *
 * @since 1.264.0000
 */
class Diagnostic_FormidableFormsPayment extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-payment';
	protected static $title = 'Formidable Forms Payment';
	protected static $description = 'Formidable Forms payment security weak';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'FrmAppHelper' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check if site is using SSL
		if ( ! is_ssl() ) {
			$issues[] = 'ssl_not_enabled';
			$threat_level += 30;
		}

		// Check payment gateway settings
		$payment_settings = get_option( 'frm_payment_settings', array() );

		// Check Stripe settings
		if ( isset( $payment_settings['stripe'] ) ) {
			$stripe = $payment_settings['stripe'];
			// Check if test mode is active in production
			if ( ! isset( $stripe['test_mode'] ) || $stripe['test_mode'] === true ) {
				$issues[] = 'stripe_test_mode_in_production';
				$threat_level += 25;
			}
			// Check webhook signature
			if ( empty( $stripe['webhook_secret'] ) ) {
				$issues[] = 'stripe_webhook_not_secured';
				$threat_level += 20;
			}
		}

		// Check PayPal settings
		if ( isset( $payment_settings['paypal'] ) ) {
			$paypal = $payment_settings['paypal'];
			// Check IPN validation
			if ( empty( $paypal['ipn_validate'] ) ) {
				$issues[] = 'paypal_ipn_not_validated';
				$threat_level += 20;
			}
		}

		// Check for payment forms
		global $wpdb;
		$payment_forms = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}frm_fields 
			 WHERE type = 'credit_card'"
		);
		if ( $payment_forms > 0 ) {
			// Check PCI compliance settings
			$pci_compliant = get_option( 'frm_pci_compliant', false );
			if ( ! $pci_compliant ) {
				$issues[] = 'pci_compliance_not_configured';
				$threat_level += 25;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of payment security issues */
				__( 'Formidable Forms payment security is weak: %s. This exposes payment data to interception and fraud.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-payment',
			);
		}
		
		return null;
	}
}
