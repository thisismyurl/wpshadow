<?php
/**
 * International Payment Methods Diagnostic
 *
 * Tests whether the site accepts region-specific payment methods preferred in
 * target markets. Supporting local payment methods increases conversion rates
 * by up to 30% in international markets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Accepts_International_Payment_Methods Class
 *
 * Diagnostic #27: International Payment Methods from Specialized & Emerging Success Habits.
 * Checks if the website accepts region-specific payment methods like Alipay, iDEAL,
 * SEPA, and other local payment preferences.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Accepts_International_Payment_Methods extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accepts-international-payment-methods';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'International Payment Methods';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site accepts region-specific payment methods preferred in target markets';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'international-ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * Regional payment preferences vary significantly. This diagnostic checks for
	 * payment gateway plugins, multiple payment methods, and documentation of
	 * accepted payment options.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Multiple payment gateway plugins.
		$payment_gateways = array(
			'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php',      // Stripe (global).
			'woocommerce-gateway-paypal-express-checkout/woocommerce-gateway-paypal-express-checkout.php', // PayPal.
			'mollie-payments-for-woocommerce/mollie-payments-for-woocommerce.php', // Mollie (EU).
			'woo-stripe-payment/stripe-payments.php',                          // Stripe alternative.
			'woocommerce-payments/woocommerce-payments.php',                   // WooCommerce Payments.
		);

		$active_gateways = 0;
		foreach ( $payment_gateways as $gateway ) {
			if ( is_plugin_active( $gateway ) ) {
				++$active_gateways;
			}
		}

		if ( $active_gateways >= 2 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of payment gateways */
				__( '✓ Multiple payment gateways active (%d+ gateways)', 'wpshadow' ),
				$active_gateways
			);
		} elseif ( $active_gateways === 1 ) {
			$score_details[]   = __( '◐ One payment gateway active', 'wpshadow' );
			$recommendations[] = __( 'Add region-specific payment gateways (Mollie for EU, Alipay for Asia)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No payment gateways detected', 'wpshadow' );
			$recommendations[] = __( 'Install payment gateway plugins to accept payments', 'wpshadow' );
		}

		// Check 2: Regional payment method references in content.
		$payment_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$regional_methods = array(
			'ideal', 'sepa', 'giropay', 'sofort', 'bancontact', 'alipay',
			'wechat pay', 'paytm', 'upi', 'pix', 'boleto', 'klarna', 'afterpay',
		);

		$mentioned_methods = 0;
		foreach ( $payment_pages as $page ) {
			$content_lower = strtolower( $page->post_content );
			foreach ( $regional_methods as $method ) {
				if ( stripos( $content_lower, $method ) !== false ) {
					++$mentioned_methods;
					break;
				}
			}
		}

		if ( $mentioned_methods >= 3 ) {
			++$score;
			$score_details[] = __( '✓ Regional payment methods referenced (iDEAL, Alipay, SEPA, etc.)', 'wpshadow' );
		} elseif ( $mentioned_methods > 0 ) {
			$score_details[]   = __( '◐ Some regional payment methods mentioned', 'wpshadow' );
			$recommendations[] = __( 'Document all accepted regional payment methods on payment/checkout pages', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No regional payment methods documented', 'wpshadow' );
			$recommendations[] = __( 'Add payment methods page listing region-specific options (iDEAL for Netherlands, Alipay for China, etc.)', 'wpshadow' );
		}

		// Check 3: Payment methods page exists.
		$payment_info_page = false;
		foreach ( $payment_pages as $page ) {
			if ( stripos( $page->post_title, 'payment' ) !== false ||
				 stripos( $page->post_title, 'checkout' ) !== false ) {
				$payment_info_page = true;
				break;
			}
		}

		if ( $payment_info_page ) {
			++$score;
			$score_details[] = __( '✓ Payment methods information page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No payment methods page found', 'wpshadow' );
			$recommendations[] = __( 'Create a payment methods page listing all accepted payment options by region', 'wpshadow' );
		}

		// Check 4: WooCommerce payment methods configured.
		if ( class_exists( 'WooCommerce' ) ) {
			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

			if ( count( $available_gateways ) >= 2 ) {
				++$score;
				$score_details[] = sprintf(
					/* translators: %d: number of enabled payment methods */
					__( '✓ Multiple WooCommerce payment methods enabled (%d+ methods)', 'wpshadow' ),
					count( $available_gateways )
				);
			} elseif ( count( $available_gateways ) === 1 ) {
				$score_details[]   = __( '◐ One payment method enabled in WooCommerce', 'wpshadow' );
				$recommendations[] = __( 'Enable additional payment methods in WooCommerce > Settings > Payments', 'wpshadow' );
			} else {
				$score_details[]   = __( '✗ No payment methods enabled', 'wpshadow' );
				$recommendations[] = __( 'Configure payment methods in WooCommerce > Settings > Payments', 'wpshadow' );
			}
		}

		// Check 5: Payment logos/icons displayed.
		$has_payment_logos = false;
		foreach ( $payment_pages as $page ) {
			if ( stripos( $page->post_content, 'visa' ) !== false ||
				 stripos( $page->post_content, 'mastercard' ) !== false ||
				 stripos( $page->post_content, 'payment-logo' ) !== false ||
				 stripos( $page->post_content, 'payment-icon' ) !== false ) {
				$has_payment_logos = true;
				break;
			}
		}

		if ( $has_payment_logos ) {
			++$score;
			$score_details[] = __( '✓ Payment method logos/icons displayed', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No payment logos found', 'wpshadow' );
			$recommendations[] = __( 'Display payment method logos to build trust and show accepted options', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			// International payment options are adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'International payment methods score: %d%%. Supporting local payment preferences increases conversion by 30%%. For example: iDEAL dominates Netherlands (60%%), Alipay/WeChat Pay dominate China (70%%), and Pix is growing rapidly in Brazil (50%% of transactions).', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/international-payment-methods?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Regional payment methods reduce cart abandonment and increase trust by offering familiar payment options customers already use.', 'wpshadow' ),
		);
	}
}
