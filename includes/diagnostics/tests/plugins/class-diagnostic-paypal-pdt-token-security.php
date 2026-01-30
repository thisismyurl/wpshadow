<?php
/**
 * Paypal Pdt Token Security Diagnostic
 *
 * Paypal Pdt Token Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1398.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paypal Pdt Token Security Diagnostic Class
 *
 * @since 1.1398.0000
 */
class Diagnostic_PaypalPdtTokenSecurity extends Diagnostic_Base {

	protected static $slug = 'paypal-pdt-token-security';
	protected static $title = 'Paypal Pdt Token Security';
	protected static $description = 'Paypal Pdt Token Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Gateway_Paypal' ) ) {
			return null;
		}

		$issues = array();

		// Check if PDT is enabled
		$pdt_enabled = get_option( 'woocommerce_paypal_pdt', 'no' );
		if ( 'yes' === $pdt_enabled ) {
			// Check for PDT token configured
			$pdt_token = get_option( 'woocommerce_paypal_identity_token', '' );
			if ( empty( $pdt_token ) ) {
				$issues[] = 'PDT enabled but identity token not configured';
			} elseif ( strlen( $pdt_token ) < 20 ) {
				$issues[] = 'PDT token appears invalid (too short)';
			}

			// Check if token is stored in database
			if ( ! empty( $pdt_token ) && ! defined( 'PAYPAL_PDT_TOKEN' ) ) {
				$issues[] = 'PDT token stored in database (use constant instead)';
			}
		}

		// Check for SSL requirement
		if ( ! is_ssl() && 'yes' === $pdt_enabled ) {
			$issues[] = 'PDT active without SSL (payment data not secure)';
		}

		// Check for IPN vs PDT
		$ipn_enabled = get_option( 'woocommerce_paypal_ipn_notification', 'yes' );
		if ( 'yes' === $pdt_enabled && 'no' === $ipn_enabled ) {
			$issues[] = 'relying on PDT alone (IPN provides better reliability)';
		}

		// Check for receiver email validation
		$receiver_email = get_option( 'woocommerce_paypal_receiver_email', '' );
		if ( 'yes' === $pdt_enabled && empty( $receiver_email ) ) {
			$issues[] = 'receiver email not set (unable to validate payments)';
		}

		// Check for PDT response logging
		global $wpdb;
		$log_enabled = get_option( 'woocommerce_paypal_debug', 'no' );
		if ( 'yes' === $log_enabled && 'yes' === $pdt_enabled ) {
			$log_dir = WP_CONTENT_DIR . '/wc-logs/';
			if ( is_dir( $log_dir ) && is_readable( $log_dir ) ) {
				$perms = fileperms( $log_dir );
				if ( ( $perms & 0044 ) > 0 ) {
					$issues[] = 'PayPal logs directory has world-readable permissions';
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'PayPal PDT token security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/paypal-pdt-token-security',
			);
		}

		return null;
	}
}
