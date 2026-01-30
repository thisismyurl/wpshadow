<?php
/**
 * Paypal Express Api Credentials Diagnostic
 *
 * Paypal Express Api Credentials vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1395.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paypal Express Api Credentials Diagnostic Class
 *
 * @since 1.1395.0000
 */
class Diagnostic_PaypalExpressApiCredentials extends Diagnostic_Base {

	protected static $slug = 'paypal-express-api-credentials';
	protected static $title = 'Paypal Express Api Credentials';
	protected static $description = 'Paypal Express Api Credentials vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Gateway_Paypal' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify PayPal is not in sandbox mode in production
		$sandbox = get_option( 'woocommerce_paypal_testmode', 'no' );
		if ( 'yes' === $sandbox && ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
			$issues[] = 'sandbox_mode_in_production';
		}
		
		// Check 2: Verify API credentials are configured
		$api_username = get_option( 'woocommerce_paypal_api_username', '' );
		$api_password = get_option( 'woocommerce_paypal_api_password', '' );
		$api_signature = get_option( 'woocommerce_paypal_api_signature', '' );
		
		if ( empty( $api_username ) || empty( $api_password ) || empty( $api_signature ) ) {
			$issues[] = 'missing_api_credentials';
		}
		
		// Check 3: Verify email address is configured
		$paypal_email = get_option( 'woocommerce_paypal_email', '' );
		if ( empty( $paypal_email ) || ! is_email( $paypal_email ) ) {
			$issues[] = 'invalid_paypal_email';
		}
		
		// Check 4: Verify IPN (Instant Payment Notification) is enabled
		$ipn_enabled = get_option( 'woocommerce_paypal_ipn_enabled', 'yes' );
		if ( 'yes' !== $ipn_enabled ) {
			$issues[] = 'ipn_disabled';
		}
		
		// Check 5: Verify receiver email matches configured email
		$receiver_email = get_option( 'woocommerce_paypal_receiver_email', '' );
		if ( ! empty( $receiver_email ) && $receiver_email !== $paypal_email ) {
			$issues[] = 'receiver_email_mismatch';
		}
		
		// Check 6: Verify SSL is required for API credentials
		if ( ! is_ssl() && 'yes' !== $sandbox ) {
			$issues[] = 'ssl_not_enabled';
		}
		
		// Check 7: Check for PDT (Payment Data Transfer) token
		$pdt_token = get_option( 'woocommerce_paypal_identity_token', '' );
		if ( empty( $pdt_token ) ) {
			$issues[] = 'pdt_token_not_configured';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of PayPal credential issues */
				__( 'PayPal Express has API credential security issues: %s. Misconfigured credentials could result in payment fraud or failed transactions.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/paypal-express-api-credentials',
			);
		}
		
		return null;
	}
}
