<?php
/**
 * WooCommerce Payment Gateway Configuration Diagnostic
 *
 * Verifies that at least one payment gateway is properly configured in WooCommerce.
 * Without configured payment methods, customers cannot complete purchases.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6029.1645
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Payment Gateway Configuration Diagnostic Class
 *
 * Checks if WooCommerce has at least one payment gateway configured.
 * Critical for e-commerce functionality - no payments = no revenue.
 *
 * @since 1.6029.1645
 */
class Diagnostic_WooCommerce_Payment_Gateway_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-payment-gateway-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Payment Gateway Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies at least one payment gateway is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$issues = array();

		// Get available payment gateways.
		$gateways = array();
		if ( function_exists( 'WC' ) && isset( WC()->payment_gateways ) ) {
			$gateways = WC()->payment_gateways->get_available_payment_gateways();
		}

		$gateway_count = count( $gateways );
		$gateway_names = array();
		$test_mode_gateways = array();

		// Analyze each gateway.
		foreach ( $gateways as $gateway_id => $gateway ) {
			$gateway_names[] = $gateway->get_title();

			// Check for test mode.
			if ( isset( $gateway->testmode ) && 'yes' === $gateway->testmode ) {
				$test_mode_gateways[] = $gateway->get_title();
			}
		}

		// Critical: No payment methods available.
		if ( 0 === $gateway_count ) {
			$issues[] = 'no_payment_gateways_enabled';
		}

		// Warning: Test mode in production.
		if ( ! empty( $test_mode_gateways ) && ! self::is_development_environment() ) {
			$issues[] = 'test_mode_active_in_production';
		}

		// Check SSL certificate (required for payments).
		if ( ! is_ssl() && $gateway_count > 0 ) {
			$issues[] = 'ssl_not_enabled_for_payments';
		}

		// Check currency settings.
		$currency = get_option( 'woocommerce_currency', '' );
		if ( empty( $currency ) ) {
			$issues[] = 'no_currency_configured';
		}

		// If issues found, return finding.
		if ( ! empty( $issues ) ) {
			$severity = 'critical';
			$threat_level = 90;

			// Lower severity if gateway exists but has minor issues.
			if ( $gateway_count > 0 && ! in_array( 'no_payment_gateways_enabled', $issues, true ) ) {
				$severity = 'high';
				$threat_level = 75;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WooCommerce payment gateway configuration is incomplete or misconfigured', 'wpshadow' ),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'details'      => array(
					'issues_found'        => $issues,
					'gateway_count'       => $gateway_count,
					'gateway_names'       => $gateway_names,
					'test_mode_gateways'  => $test_mode_gateways,
					'ssl_enabled'         => is_ssl(),
					'currency'            => $currency,
					'is_dev_environment'  => self::is_development_environment(),
				),
				'meta'         => array(
					'wpdb_avoidance'   => 'Uses WooCommerce API, get_option(), is_ssl() instead of $wpdb',
					'detection_method' => 'WooCommerce API - gateway enumeration, configuration checks',
					'business_impact'  => 'No payment methods = no revenue',
				),
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-payment-gateway-configuration',
				'solution'     => sprintf(
					/* translators: 1: WooCommerce settings URL */
					__( 'WooCommerce requires at least one configured payment gateway. Issues found: %1$s. Actions needed: 1) Go to WooCommerce → Settings → Payments at %2$s, 2) Enable at least one payment method (Stripe, PayPal, etc.), 3) Configure API credentials for chosen gateway, 4) If using test mode, switch to live mode in production, 5) Ensure SSL certificate is active (HTTPS), 6) Set store currency at WooCommerce → Settings → General, 7) Test checkout process to verify payments work. Common gateways: Stripe (credit cards), PayPal (buyer accounts), Square (POS integration). Learn more: <a href="https://woocommerce.com/document/woocommerce-payment-gateways/">WooCommerce Payment Gateways</a>', 'wpshadow' ),
					implode( ', ', $issues ),
					esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) )
				),
			);
		}

		return null;
	}

	/**
	 * Check if running in development environment.
	 *
	 * @since  1.6029.1645
	 * @return bool True if development environment, false otherwise.
	 */
	private static function is_development_environment() {
		// Check WP_DEBUG.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		// Check WP_ENVIRONMENT_TYPE (WP 5.5+).
		if ( function_exists( 'wp_get_environment_type' ) ) {
			$env = wp_get_environment_type();
			if ( in_array( $env, array( 'local', 'development' ), true ) ) {
				return true;
			}
		}

		// Check localhost domains.
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$local_domains = array( 'localhost', '.local', '.test', '.dev', '127.0.0.1' );

		foreach ( $local_domains as $local_domain ) {
			if ( stripos( $host, $local_domain ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
