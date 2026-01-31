<?php
/**
 * Single Payment Method Risk Diagnostic
 *
 * Detects if only one payment gateway is available on an e-commerce site,
 * which limits customer options and may reduce conversion rates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single Payment Method Risk Diagnostic Class
 *
 * Checks for payment method diversity to ensure customers have options
 * that match their payment preferences.
 *
 * @since 1.6028.1445
 */
class Diagnostic_Single_Payment_Method_Risk extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'single-payment-method-risk';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Single Payment Method Risk';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects if only one payment gateway is available, limiting customer payment options';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_payment_method_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$result = self::check_payment_methods();

		// Cache for 6 hours.
		set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Check payment method availability.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	private static function check_payment_methods() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			// Check for EDD.
			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				return self::check_edd_payment_methods();
			}

			return null; // No e-commerce plugin.
		}

		// Get active WooCommerce payment gateways.
		$payment_gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( empty( $payment_gateways ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => __( 'No Payment Methods Enabled', 'wpshadow' ),
				'description'  => __( 'No payment methods are currently enabled for checkout', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/payment-methods',
				'meta'         => array(
					'payment_count' => 0,
					'ecommerce'     => 'woocommerce',
				),
				'details'      => array(
					__( 'No payment methods enabled - customers cannot complete purchases', 'wpshadow' ),
					__( 'Configure at least one payment gateway immediately', 'wpshadow' ),
				),
				'recommendations' => array(
					__( 'URGENT: Enable at least one payment gateway', 'wpshadow' ),
					__( 'Configure Stripe or PayPal for quick setup', 'wpshadow' ),
				),
			);
		}

		$payment_count = count( $payment_gateways );

		// Get details about available payment methods.
		$payment_types = self::categorize_payment_methods( $payment_gateways );

		// Check thresholds.
		if ( $payment_count >= 3 ) {
			return null; // 3+ methods is good.
		} elseif ( $payment_count >= 2 ) {
			// 2 methods is acceptable but could be better.
			return null;
		}

		// Only 1 payment method - flag it.
		$severity     = 'medium';
		$threat_level = 55;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: payment method name */
				__( 'Only one payment method available (%s), limiting customer options', 'wpshadow' ),
				self::get_payment_method_name( array_keys( $payment_gateways )[0] )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/payment-methods',
			'meta'         => array(
				'payment_count'      => $payment_count,
				'available_methods'  => array_keys( $payment_gateways ),
				'payment_types'      => $payment_types,
				'ecommerce'          => 'woocommerce',
			),
			'details'      => self::get_payment_details( $payment_types ),
			'recommendations' => self::get_recommendations( $payment_types ),
		);
	}

	/**
	 * Check EDD payment methods.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	private static function check_edd_payment_methods() {
		if ( ! function_exists( 'edd_get_enabled_payment_gateways' ) ) {
			return null;
		}

		$payment_gateways = edd_get_enabled_payment_gateways();

		if ( empty( $payment_gateways ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => __( 'No Payment Methods Enabled', 'wpshadow' ),
				'description'  => __( 'No payment methods are currently enabled for checkout', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/payment-methods',
				'meta'         => array(
					'payment_count' => 0,
					'ecommerce'     => 'edd',
				),
				'details'      => array(
					__( 'No payment methods enabled in Easy Digital Downloads', 'wpshadow' ),
				),
				'recommendations' => array(
					__( 'URGENT: Enable at least one payment gateway', 'wpshadow' ),
				),
			);
		}

		$payment_count = count( $payment_gateways );

		if ( $payment_count >= 2 ) {
			return null; // 2+ methods is acceptable.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Only one payment method available in Easy Digital Downloads', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/payment-methods',
			'meta'         => array(
				'payment_count'     => $payment_count,
				'available_methods' => array_keys( $payment_gateways ),
				'ecommerce'         => 'edd',
			),
			'details'      => array(
				__( 'Single payment method limits customer options', 'wpshadow' ),
			),
			'recommendations' => array(
				__( 'Add additional payment methods (PayPal, Stripe, etc.)', 'wpshadow' ),
			),
		);
	}

	/**
	 * Categorize payment methods by type.
	 *
	 * @since  1.6028.1445
	 * @param  array $gateways Payment gateways.
	 * @return array Payment types.
	 */
	private static function categorize_payment_methods( $gateways ) {
		$types = array(
			'credit_card'    => false,
			'paypal'         => false,
			'digital_wallet' => false,
			'bank_transfer'  => false,
			'cash_on_delivery' => false,
			'other'          => false,
		);

		foreach ( array_keys( $gateways ) as $gateway_id ) {
			switch ( $gateway_id ) {
				case 'stripe':
				case 'stripe_cc':
				case 'authorize_net':
				case 'square':
					$types['credit_card'] = true;
					break;

				case 'paypal':
				case 'ppec_paypal':
				case 'paypal_pro':
					$types['paypal'] = true;
					break;

				case 'apple_pay':
				case 'google_pay':
				case 'amazon_pay':
					$types['digital_wallet'] = true;
					break;

				case 'bacs':
				case 'direct-debit':
					$types['bank_transfer'] = true;
					break;

				case 'cod':
					$types['cash_on_delivery'] = true;
					break;

				default:
					$types['other'] = true;
					break;
			}
		}

		return $types;
	}

	/**
	 * Get human-readable payment method name.
	 *
	 * @since  1.6028.1445
	 * @param  string $gateway_id Gateway ID.
	 * @return string Human-readable name.
	 */
	private static function get_payment_method_name( $gateway_id ) {
		$names = array(
			'stripe'       => 'Stripe',
			'paypal'       => 'PayPal',
			'ppec_paypal'  => 'PayPal Checkout',
			'bacs'         => 'Bank Transfer',
			'cod'          => 'Cash on Delivery',
			'cheque'       => 'Check',
			'square'       => 'Square',
			'apple_pay'    => 'Apple Pay',
			'google_pay'   => 'Google Pay',
		);

		return $names[ $gateway_id ] ?? ucfirst( str_replace( '_', ' ', $gateway_id ) );
	}

	/**
	 * Get payment details.
	 *
	 * @since  1.6028.1445
	 * @param  array $payment_types Payment types.
	 * @return array Details.
	 */
	private static function get_payment_details( $payment_types ) {
		$details = array(
			__( 'Limited payment options reduce conversion rates', 'wpshadow' ),
			__( 'Different customers prefer different payment methods', 'wpshadow' ),
		);

		// Identify missing payment types.
		if ( ! $payment_types['credit_card'] ) {
			$details[] = __( 'Missing: Credit/debit card payment option', 'wpshadow' );
		}

		if ( ! $payment_types['paypal'] ) {
			$details[] = __( 'Missing: PayPal payment option', 'wpshadow' );
		}

		if ( ! $payment_types['digital_wallet'] ) {
			$details[] = __( 'Missing: Digital wallet options (Apple Pay, Google Pay)', 'wpshadow' );
		}

		return $details;
	}

	/**
	 * Get recommendations based on payment types.
	 *
	 * @since  1.6028.1445
	 * @param  array $payment_types Payment types.
	 * @return array Recommendations.
	 */
	private static function get_recommendations( $payment_types ) {
		$recommendations = array();

		if ( ! $payment_types['credit_card'] ) {
			$recommendations[] = __( 'Add Stripe or Square for credit card payments', 'wpshadow' );
		}

		if ( ! $payment_types['paypal'] ) {
			$recommendations[] = __( 'Add PayPal for customer convenience', 'wpshadow' );
		}

		if ( ! $payment_types['digital_wallet'] ) {
			$recommendations[] = __( 'Consider adding Apple Pay or Google Pay for mobile users', 'wpshadow' );
		}

		// General recommendations.
		$recommendations[] = __( 'Research payment preferences in your target markets', 'wpshadow' );
		$recommendations[] = __( 'Consider regional payment methods for international customers', 'wpshadow' );
		$recommendations[] = __( 'Aim for at least 3 payment options for optimal conversion', 'wpshadow' );
		$recommendations[] = __( 'Display accepted payment methods prominently on product pages', 'wpshadow' );

		return $recommendations;
	}
}
