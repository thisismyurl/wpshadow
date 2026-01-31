<?php
/**
 * Paypal Standard Ipn Validation Diagnostic
 *
 * Paypal Standard Ipn Validation vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1394.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paypal Standard Ipn Validation Diagnostic Class
 *
 * @since 1.1394.0000
 */
class Diagnostic_PaypalStandardIpnValidation extends Diagnostic_Base {

	protected static $slug = 'paypal-standard-ipn-validation';
	protected static $title = 'Paypal Standard Ipn Validation';
	protected static $description = 'Paypal Standard Ipn Validation vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Gateway_Paypal' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify IPN is enabled
		$ipn_enabled = get_option( 'woocommerce_paypal_ipn_enabled', 'yes' );
		if ( 'yes' !== $ipn_enabled ) {
			$issues[] = 'ipn_not_enabled';
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'PayPal IPN (Instant Payment Notification) is disabled. Payment confirmations will not be validated, allowing potential fraud.', 'wpshadow' ),
				'severity'     => 75,
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/paypal-standard-ipn-validation',
			);
		}
		
		// Check 2: Verify receiver email is configured for validation
		$receiver_email = get_option( 'woocommerce_paypal_receiver_email', '' );
		$paypal_email = get_option( 'woocommerce_paypal_email', '' );
		
		if ( empty( $receiver_email ) ) {
			$issues[] = 'receiver_email_not_configured';
		}
		
		// Check 3: Verify IPN notification URL is accessible
		$ipn_url = WC()->api_request_url( 'WC_Gateway_Paypal' );
		if ( empty( $ipn_url ) ) {
			$issues[] = 'ipn_url_not_configured';
		}
		
		// Check 4: Verify IPN URL uses HTTPS
		if ( ! empty( $ipn_url ) && strpos( $ipn_url, 'https://' ) !== 0 ) {
			$issues[] = 'ipn_url_not_secure';
		}
		
		// Check 5: Check if IPN validation is bypassed (security risk)
		$debug_mode = get_option( 'woocommerce_paypal_debug', 'no' );
		if ( 'yes' === $debug_mode && ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
			$issues[] = 'debug_mode_in_production';
		}
		
		// Check 6: Verify PayPal business email matches receiver email
		if ( ! empty( $receiver_email ) && ! empty( $paypal_email ) && $receiver_email !== $paypal_email ) {
			$issues[] = 'email_mismatch';
		}
		
		// Check 7: Check for recent IPN failures in logs
		global $wpdb;
		$ipn_failures = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_log 
				WHERE source = %s AND level >= %d AND timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY)",
				'paypal-ipn',
				400 // Error level
			)
		);
		
		if ( $ipn_failures > 10 ) {
			$issues[] = 'recent_ipn_validation_failures';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of IPN validation issues */
				__( 'PayPal IPN validation has security issues: %s. Invalid IPN handling can allow fraudulent payment confirmations.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 75,
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/paypal-standard-ipn-validation',
			);
		}
		
		return null;
	}
}
