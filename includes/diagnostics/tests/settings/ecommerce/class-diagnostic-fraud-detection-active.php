<?php
/**
 * Fraud Detection Active Diagnostic
 *
 * Checks if payment fraud detection is enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fraud Detection Active Diagnostic Class
 *
 * Verifies that fraud detection measures are active to protect
 * against fraudulent transactions.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Fraud_Detection_Active extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'fraud-detection-active';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Fraud Detection Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if payment fraud detection is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the fraud detection diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if fraud detection issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping fraud detection check', 'wpshadow' );
			return null;
		}

		// Check for Stripe fraud detection.
		$stripe_key = get_option( 'woocommerce_stripe_settings' );
		$has_stripe = ! empty( $stripe_key );
		$stats['stripe_enabled'] = $has_stripe;

		if ( $has_stripe ) {
			$stripe_settings = maybe_unserialize( $stripe_key );
			if ( is_array( $stripe_settings ) ) {
				$stats['stripe_fraud_detection'] = $stripe_settings['stripe_threat_level'] ?? 'default';
			}
		}

		// Check for PayPal fraud detection.
		$paypal_key = get_option( 'woocommerce_paypal_settings' );
		$has_paypal = ! empty( $paypal_key );
		$stats['paypal_enabled'] = $has_paypal;

		// Check for AVS (Address Verification System).
		$avs_enabled = get_option( 'woocommerce_enable_avs' );
		$stats['avs_enabled'] = boolval( $avs_enabled );

		if ( ! $avs_enabled ) {
			$warnings[] = __( 'Address Verification System (AVS) not enabled - reduces fraud detection', 'wpshadow' );
		}

		// Check for CVV (Card Verification Value) requirement.
		$cvv_required = get_option( 'woocommerce_require_cvv' );
		$stats['cvv_required'] = boolval( $cvv_required );

		if ( ! $cvv_required ) {
			$warnings[] = __( 'CVV not required - increases fraud risk', 'wpshadow' );
		}

		// Check for 3D Secure.
		$three_d_secure = get_option( 'woocommerce_3d_secure_enabled' );
		$stats['3d_secure_enabled'] = boolval( $three_d_secure );

		if ( ! $three_d_secure ) {
			$warnings[] = __( '3D Secure not enabled - recommended for fraud prevention', 'wpshadow' );
		}

		// Check for IP verification.
		$ip_verification = get_option( 'woocommerce_verify_customer_ip' );
		$stats['ip_verification'] = boolval( $ip_verification );

		// Check for velocity checking (repeated failed attempts).
		$max_failed_attempts = get_option( 'woocommerce_max_failed_payments', 3 );
		$stats['max_failed_attempts'] = intval( $max_failed_attempts );

		if ( $max_failed_attempts > 5 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'Allowing %d failed payment attempts before lockout - too permissive', 'wpshadow' ),
				$max_failed_attempts
			);
		}

		// Check for rate limiting.
		$rate_limit_enabled = get_option( 'woocommerce_rate_limit_enabled' );
		$stats['rate_limiting'] = boolval( $rate_limit_enabled );

		if ( ! $rate_limit_enabled ) {
			$warnings[] = __( 'Rate limiting not enabled - vulnerable to brute force attacks', 'wpshadow' );
		}

		// Check for duplicate order detection.
		$duplicate_detection = get_option( 'woocommerce_duplicate_order_detection' );
		$stats['duplicate_detection'] = boolval( $duplicate_detection );

		if ( ! $duplicate_detection ) {
			$warnings[] = __( 'Duplicate order detection not enabled', 'wpshadow' );
		}

		// Check for suspicious order notifications.
		$suspicious_orders = get_option( 'woocommerce_notify_suspicious_orders' );
		$stats['suspicious_notification'] = boolval( $suspicious_orders );

		if ( ! $suspicious_orders ) {
			$warnings[] = __( 'Suspicious order notifications disabled', 'wpshadow' );
		}

		// Check for geolocation blocking.
		$block_countries = get_option( 'woocommerce_blocked_countries' );
		$stats['country_blocking'] = ! empty( $block_countries );

		// Check for high-value order alerts.
		$high_value_threshold = get_option( 'woocommerce_high_value_order_threshold' );
		$stats['high_value_alerts'] = ! empty( $high_value_threshold );

		if ( empty( $high_value_threshold ) ) {
			$warnings[] = __( 'High-value order alerts not configured', 'wpshadow' );
		}

		// Check for fraud detection plugins.
		$fraud_plugins = array(
			'advanced-order-protection/advanced-order-protection.php',
			'woo-guard/woo-guard.php',
			'aliexpress-order-protection/plugin.php',
		);

		$has_fraud_plugin = false;
		foreach ( $fraud_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_fraud_plugin = true;
				break;
			}
		}

		$stats['fraud_detection_plugin'] = $has_fraud_plugin;

		if ( ! $has_fraud_plugin && ! $has_stripe && ! $has_paypal ) {
			$issues[] = __( 'No fraud detection methods active - store is vulnerable', 'wpshadow' );
		}

		// Check for SSL certificate.
		$ssl_enabled = is_ssl();
		$stats['ssl_certificate'] = $ssl_enabled;

		if ( ! $ssl_enabled ) {
			$issues[] = __( 'SSL certificate not active - payment data not encrypted', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Fraud detection has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/fraud-detection-active',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Fraud detection has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/fraud-detection-active',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Fraud detection is active.
	}
}
