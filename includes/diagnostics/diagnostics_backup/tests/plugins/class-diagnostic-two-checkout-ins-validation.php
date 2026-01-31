<?php
/**
 * Two Checkout Ins Validation Diagnostic
 *
 * Two Checkout Ins Validation vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1416.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Two Checkout Ins Validation Diagnostic Class
 *
 * @since 1.1416.0000
 */
class Diagnostic_TwoCheckoutInsValidation extends Diagnostic_Base {

	protected static $slug = 'two-checkout-ins-validation';
	protected static $title = 'Two Checkout Ins Validation';
	protected static $description = 'Two Checkout Ins Validation vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		// Check if 2Checkout gateway is enabled
		$payment_gateways = WC()->payment_gateways ? WC()->payment_gateways->payment_gateways() : array();
		$twocheckout_enabled = false;
		foreach ( $payment_gateways as $gateway ) {
			if ( strpos( get_class( $gateway ), 'TwoCheckout' ) !== false || strpos( get_class( $gateway ), '2Checkout' ) !== false ) {
				$twocheckout_enabled = true;
				break;
			}
		}

		if ( ! $twocheckout_enabled ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check API credentials
		$api_key = get_option( 'woocommerce_twocheckout_seller_id', '' );
		$secret_word = get_option( 'woocommerce_twocheckout_secret_word', '' );
		if ( empty( $api_key ) || empty( $secret_word ) ) {
			$issues[] = 'api_credentials_not_configured';
			$threat_level += 35;
		}

		// Check SSL
		if ( ! is_ssl() ) {
			$issues[] = 'ssl_not_enabled';
			$threat_level += 35;
		}

		// Check INS validation
		$validate_ins = get_option( 'woocommerce_twocheckout_validate_ins', 'yes' );
		if ( $validate_ins === 'no' ) {
			$issues[] = 'ins_validation_disabled';
			$threat_level += 40;
		}

		// Check MD5 hash validation
		$md5_hash = get_option( 'woocommerce_twocheckout_secret_word', '' );
		if ( empty( $md5_hash ) ) {
			$issues[] = 'md5_hash_not_configured';
			$threat_level += 30;
		}

		// Check sandbox mode in production
		$sandbox_mode = get_option( 'woocommerce_twocheckout_sandbox', 'no' );
		if ( $sandbox_mode === 'yes' ) {
			$issues[] = 'sandbox_mode_in_production';
			$threat_level += 25;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of validation issues */
				__( '2Checkout INS validation has critical security issues: %s. This allows payment fraud and unauthorized transactions.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/two-checkout-ins-validation',
			);
		}
		
		return null;
	}
}
