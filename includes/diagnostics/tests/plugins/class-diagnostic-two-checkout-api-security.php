<?php
/**
 * Two Checkout Api Security Diagnostic
 *
 * Two Checkout Api Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1415.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Two Checkout Api Security Diagnostic Class
 *
 * @since 1.1415.0000
 */
class Diagnostic_TwoCheckoutApiSecurity extends Diagnostic_Base {

	protected static $slug = 'two-checkout-api-security';
	protected static $title = 'Two Checkout Api Security';
	protected static $description = 'Two Checkout Api Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		// Check for 2Checkout integration
		$has_2co = get_option( 'twocheckout_merchant_code', '' ) ||
		           get_option( 'twocheckout_api_key', '' ) ||
		           class_exists( 'TwoCheckout' );
		
		if ( ! $has_2co ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API credentials configured
		$api_key = get_option( 'twocheckout_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( '2Checkout API key not configured', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( '2Checkout not connected', 'wpshadow' ),
				'severity'    => 85,
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/two-checkout-api-security',
			);
		}
		
		// Check 2: API credentials in database
		if ( ! defined( 'TWOCHECKOUT_API_KEY' ) ) {
			$issues[] = __( 'API key in database (should be in wp-config.php)', 'wpshadow' );
		}
		
		// Check 3: Test mode in production
		$test_mode = get_option( 'twocheckout_sandbox_mode', false );
		if ( $test_mode && ( ! defined( 'WP_ENV' ) || 'production' === WP_ENV ) ) {
			$issues[] = __( 'Sandbox mode active in production (no real payments)', 'wpshadow' );
		}
		
		// Check 4: Webhook signature verification
		$verify_webhook = get_option( 'twocheckout_verify_webhook', true );
		if ( ! $verify_webhook ) {
			$issues[] = __( 'Webhook signature not verified (fraud risk)', 'wpshadow' );
		}
		
		// Check 5: Secret word configured
		$secret_word = get_option( 'twocheckout_secret_word', '' );
		if ( empty( $secret_word ) ) {
			$issues[] = __( 'Secret word not configured (callback verification fail)', 'wpshadow' );
		}
		
		// Check 6: SSL enforcement
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using SSL (PCI DSS violation)', 'wpshadow' );
		}
		
		// Check 7: Transaction logging
		$log_transactions = get_option( 'twocheckout_log_transactions', false );
		if ( ! $log_transactions ) {
			$issues[] = __( 'Transaction logging disabled (no audit trail)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 80;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 92;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 86;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of 2Checkout security issues */
				__( '2Checkout API has %d critical security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/two-checkout-api-security',
		);
	}
}
