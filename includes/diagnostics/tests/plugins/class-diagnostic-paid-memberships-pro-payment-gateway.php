<?php
/**
 * Paid Memberships Pro Payment Gateway Diagnostic
 *
 * PMPro payment gateways insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.333.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paid Memberships Pro Payment Gateway Diagnostic Class
 *
 * @since 1.333.0000
 */
class Diagnostic_PaidMembershipsProPaymentGateway extends Diagnostic_Base {

	protected static $slug = 'paid-memberships-pro-payment-gateway';
	protected static $title = 'Paid Memberships Pro Payment Gateway';
	protected static $description = 'PMPro payment gateways insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'PMPRO_VERSION' ) ) {
			return null;
		}
		
		// Check if PMPro is active
		if ( ! defined( 'PMPRO_VERSION' ) && ! function_exists( 'pmpro_getGateway' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check SSL
		if ( ! is_ssl() ) {
			$issues[] = 'ssl_not_enabled';
			$threat_level += 40;
		}

		// Check gateway configuration
		$gateway = get_option( 'pmpro_gateway', '' );
		if ( empty( $gateway ) ) {
			$issues[] = 'no_gateway_configured';
			$threat_level += 35;
		}

		// Check API credentials
		if ( ! empty( $gateway ) ) {
			$gateway_key = get_option( 'pmpro_' . $gateway . '_apikey', '' );
			$gateway_secret = get_option( 'pmpro_' . $gateway . '_secretkey', '' );
			if ( empty( $gateway_key ) || empty( $gateway_secret ) ) {
				$issues[] = 'gateway_credentials_missing';
				$threat_level += 35;
			}
		}

		// Check sandbox mode
		$gateway_environment = get_option( 'pmpro_gateway_environment', '' );
		if ( $gateway_environment === 'sandbox' ) {
			$issues[] = 'sandbox_mode_in_production';
			$threat_level += 30;
		}

		// Check webhook security
		$webhook_key = get_option( 'pmpro_webhook_key', '' );
		if ( empty( $webhook_key ) ) {
			$issues[] = 'webhook_key_not_configured';
			$threat_level += 25;
		}

		// Check transaction logging
		$ipn_debug = get_option( 'pmpro_ipn_debug', '0' );
		if ( $ipn_debug === '0' ) {
			$issues[] = 'transaction_logging_disabled';
			$threat_level += 15;
		}

		// Check payment retry
		global $wpdb;
		$failed_orders = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->pmpro_membership_orders}
				 WHERE status = %s",
				'error'
			)
		);
		if ( $failed_orders > 10 ) {
			$issues[] = 'excessive_failed_orders';
			$threat_level += 20;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of security issues */
				__( 'Paid Memberships Pro payment gateways have security vulnerabilities: %s. This exposes payment data and enables fraud.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/paid-memberships-pro-payment-gateway',
			);
		}
		
		return null;
	}
}
