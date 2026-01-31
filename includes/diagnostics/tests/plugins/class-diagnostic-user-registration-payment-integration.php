<?php
/**
 * User Registration Payment Integration Diagnostic
 *
 * User Registration Payment Integration issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1229.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Registration Payment Integration Diagnostic Class
 *
 * @since 1.1229.0000
 */
class Diagnostic_UserRegistrationPaymentIntegration extends Diagnostic_Base {

	protected static $slug = 'user-registration-payment-integration';
	protected static $title = 'User Registration Payment Integration';
	protected static $description = 'User Registration Payment Integration issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'user_registration' ) && ! class_exists( 'UserRegistration' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Payment gateway SSL.
		if ( ! is_ssl() ) {
			$issues[] = 'payments without HTTPS';
		}
		
		// Check 2: Gateway configuration.
		$gateway = get_option( 'user_registration_payment_gateway', '' );
		if ( empty( $gateway ) ) {
			$issues[] = 'payment gateway not configured';
		}
		
		// Check 3: API key security.
		$api_key = get_option( 'user_registration_payment_api_key', '' );
		if ( ! empty( $api_key ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = 'API keys visible with debug mode';
		}
		
		// Check 4: Payment logging.
		$payment_log = get_option( 'user_registration_payment_logging', '1' );
		if ( '0' === $payment_log ) {
			$issues[] = 'payment logging disabled';
		}
		
		// Check 5: Failed payment handling.
		$failed_handling = get_option( 'user_registration_handle_failed_payments', '1' );
		if ( '0' === $failed_handling ) {
			$issues[] = 'failed payments not handled';
		}
		
		// Check 6: Payment verification.
		$verify = get_option( 'user_registration_verify_payments', '1' );
		if ( '0' === $verify ) {
			$issues[] = 'payment verification disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 70 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'User registration payment issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/user-registration-payment-integration',
			);
		}
		
		return null;
	}
}
