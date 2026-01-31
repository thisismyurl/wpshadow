<?php
/**
 * Paypal Sandbox Mode Detection Diagnostic
 *
 * Paypal Sandbox Mode Detection vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1399.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paypal Sandbox Mode Detection Diagnostic Class
 *
 * @since 1.1399.0000
 */
class Diagnostic_PaypalSandboxModeDetection extends Diagnostic_Base {

	protected static $slug = 'paypal-sandbox-mode-detection';
	protected static $title = 'Paypal Sandbox Mode Detection';
	protected static $description = 'Paypal Sandbox Mode Detection vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Gateway_Paypal' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Sandbox mode enabled
		$sandbox = get_option( 'woocommerce_paypal_settings', array() );
		$testmode = isset( $sandbox['testmode'] ) ? $sandbox['testmode'] : 'no';

		if ( 'yes' === $testmode ) {
			$issues[] = __( 'PayPal sandbox mode enabled (test payments)', 'wpshadow' );
		}

		// Check 2: Sandbox credentials in production
		$api_username = isset( $sandbox['api_username'] ) ? $sandbox['api_username'] : '';
		if ( strpos( $api_username, 'sandbox' ) !== false ) {
			$issues[] = __( 'Sandbox credentials detected (payments fail)', 'wpshadow' );
		}

		// Check 3: Email address
		$email = isset( $sandbox['email'] ) ? $sandbox['email'] : '';
		if ( strpos( $email, 'sandbox' ) !== false || strpos( $email, 'test' ) !== false ) {
			$issues[] = __( 'Test email address (revenue loss)', 'wpshadow' );
		}

		// Check 4: Debug logging
		$debug = isset( $sandbox['debug'] ) ? $sandbox['debug'] : 'no';
		if ( 'yes' === $debug && 'yes' !== $testmode ) {
			$issues[] = __( 'Debug logging in production (performance hit)', 'wpshadow' );
		}

		// Check 5: IPN validation
		$ipn_url = isset( $sandbox['ipn_notification_url'] ) ? $sandbox['ipn_notification_url'] : '';
		if ( strpos( $ipn_url, 'sandbox' ) !== false ) {
			$issues[] = __( 'Sandbox IPN URL (payment notifications fail)', 'wpshadow' );
		}

		// Check 6: Currency restrictions
		$currency = get_woocommerce_currency();
		if ( 'yes' === $testmode && $currency !== 'USD' ) {
			$issues[] = sprintf( __( 'Sandbox with %s currency (limited testing)', 'wpshadow' ), $currency );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 75;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 87;
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = 81;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'PayPal has %d sandbox/production issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/paypal-sandbox-mode-detection',
		);
	}
}
