<?php
/**
 * WooCommerce Security Configuration Diagnostic
 *
 * Tests if WooCommerce has proper security measures configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Security Configuration Diagnostic Class
 *
 * Validates that WooCommerce has proper security measures including
 * SSL for checkout, secure payment gateways, and fraud prevention.
 *
 * @since 1.6093.1200
 */
class Diagnostic_WooCommerce_Security_Configuration extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-security-configuration';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Security Configuration';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if WooCommerce has proper security measures configured';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests WooCommerce security including SSL, payment gateway security,
	 * checkout protection, and PCI compliance measures.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null; // Not applicable if WooCommerce not installed.
		}

		// Check SSL configuration.
		$force_ssl_checkout = get_option( 'woocommerce_force_ssl_checkout' );
		$is_ssl = is_ssl();

		// Check if entire site is HTTPS.
		$site_url = get_option( 'siteurl' );
		$site_is_https = ( strpos( $site_url, 'https://' ) === 0 );

		// Check payment gateways.
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		$insecure_gateways = array();

		foreach ( $available_gateways as $gateway ) {
			$gateway_id = $gateway->id ?? '';
			// Check for insecure gateways (direct bank transfer, COD without fraud checks).
			if ( in_array( $gateway_id, array( 'bacs', 'cod', 'cheque' ), true ) ) {
				$insecure_gateways[] = $gateway->title ?? $gateway_id;
			}
		}

		// Check for security plugins.
		$has_security_plugin = is_plugin_active( 'wordfence/wordfence.php' ) ||
							  is_plugin_active( 'sucuri-scanner/sucuri.php' ) ||
							  is_plugin_active( 'better-wp-security/better-wp-security.php' );

		// Check for fraud prevention plugins.
		$has_fraud_prevention = is_plugin_active( 'woocommerce-anti-fraud/woocommerce-anti-fraud.php' );

		// Check checkout page security.
		$checkout_page_id = wc_get_page_id( 'checkout' );
		$checkout_page = get_post( $checkout_page_id );
		$checkout_published = ( $checkout_page && 'publish' === $checkout_page->post_status );

		// Check for secure customer data storage.
		$customer_data_secure = get_option( 'woocommerce_keep_shipping_data' ) === 'no';

		// Check for cart abandonment protection.
		$session_handler = get_option( 'woocommerce_session_handler' );

		// Check for two-factor authentication.
		$has_2fa = is_plugin_active( 'two-factor/two-factor.php' ) ||
				  is_plugin_active( 'wordfence/wordfence.php' );

		// Check admin access restrictions.
		$restrict_admin = get_option( 'woocommerce_lock_down_admin' );

		// Count recent orders for risk assessment.
		global $wpdb;
		$recent_orders = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_type = 'shop_order'
			 AND post_date > DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);

		// Check for issues.
		$issues = array();

		// Issue 1: SSL not forced for checkout.
		if ( 'yes' !== $force_ssl_checkout && ! $site_is_https ) {
			$issues[] = array(
				'type'        => 'ssl_not_forced',
				'description' => __( 'Adding SSL encryption to your checkout protects customer payment information (like using a sealed envelope instead of a postcard). This shows the padlock icon in browsers and keeps credit card details private.', 'wpshadow' ),
			);
		}

		// Issue 2: Site not fully HTTPS.
		if ( ! $site_is_https ) {
			$issues[] = array(
				'type'        => 'site_not_https',
				'description' => __( 'Site not fully HTTPS; mixed content warnings and security issues possible', 'wpshadow' ),
			);
		}

		// Issue 3: Insecure payment gateways without fraud checks.
		if ( ! empty( $insecure_gateways ) && absint( $recent_orders ) > 100 ) {
			$issues[] = array(
				'type'        => 'insecure_gateways',
				'description' => sprintf(
					/* translators: %s: list of gateway names */
					__( 'High-risk payment methods enabled: %s', 'wpshadow' ),
					implode( ', ', $insecure_gateways )
				),
			);
		}

		// Issue 4: No fraud prevention plugin.
		if ( ! $has_fraud_prevention && absint( $recent_orders ) > 50 ) {
			$issues[] = array(
				'type'        => 'no_fraud_prevention',
				'description' => __( 'No fraud prevention system; store vulnerable to fraudulent orders', 'wpshadow' ),
			);
		}

		// Issue 5: No two-factor authentication for admin accounts.
		if ( ! $has_2fa ) {
			$issues[] = array(
				'type'        => 'no_2fa',
				'description' => __( 'Two-factor authentication not enabled; admin accounts vulnerable to compromise', 'wpshadow' ),
			);
		}

		// Issue 6: Customer shipping data stored indefinitely.
		if ( ! $customer_data_secure ) {
			$issues[] = array(
				'type'        => 'customer_data_retention',
				'description' => __( 'Customer shipping data stored indefinitely; GDPR compliance risk', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WooCommerce security measures are not properly configured, putting customer data and transactions at risk', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-security-configuration',
				'details'      => array(
					'force_ssl_checkout'      => $force_ssl_checkout,
					'is_ssl'                  => $is_ssl,
					'site_is_https'           => $site_is_https,
					'available_gateways'      => array_keys( $available_gateways ),
					'insecure_gateways'       => $insecure_gateways,
					'has_security_plugin'     => $has_security_plugin,
					'has_fraud_prevention'    => $has_fraud_prevention,
					'has_2fa'                 => $has_2fa,
					'checkout_published'      => $checkout_published,
					'customer_data_secure'    => $customer_data_secure,
					'recent_orders_count'     => absint( $recent_orders ),
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Enable SSL for entire site, install fraud prevention, enable 2FA, use secure payment gateways', 'wpshadow' ),
					'pci_compliance_checklist' => array(
						'Force HTTPS for entire site',
						'Use secure payment gateways only (Stripe, PayPal)',
						'Never store credit card data locally',
						'Enable fraud prevention',
						'Restrict admin access with 2FA',
						'Keep WooCommerce and plugins updated',
						'Use security monitoring plugin',
					),
					'secure_payment_gateways' => array(
						'Stripe'       => 'PCI compliant, tokenization, fraud detection',
						'PayPal'       => 'PCI compliant, buyer protection',
						'Square'       => 'PCI compliant, encrypted payments',
						'Authorize.net' => 'PCI compliant, fraud filters',
					),
					'compliance_impact'       => 'PCI-DSS compliance required for payment processing; fines up to $500,000 for breaches',
				),
			);
		}

		return null;
	}
}
