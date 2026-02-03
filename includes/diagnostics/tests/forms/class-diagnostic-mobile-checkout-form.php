<?php
/**
 * Mobile Checkout Form Optimization
 *
 * Validates checkout forms for mobile usability and conversion.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forms
 * @since      1.2602.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Checkout Form Optimization
 *
 * Validates that checkout forms are optimized for mobile users,
 * including multi-step flows, field optimization, and payment methods.
 *
 * @since 1.2602.1445
 */
class Diagnostic_Mobile_Checkout_Form extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-checkout-form-optimization';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Checkout Form Optimization';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates checkout forms for mobile usability';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce or EDD is active
		if ( ! self::has_ecommerce_plugin() ) {
			return null; // Not applicable
		}

		$issues = self::find_checkout_issues();

		if ( empty( $issues['all'] ) ) {
			return null;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of checkout optimization issues */
				__( 'Found %d mobile checkout optimization issues', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'        => 'high',
			'threat_level'    => 70,
			'issues'          => $issues['all'],
			'user_impact'     => __( 'Mobile users abandon checkout due to usability issues', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/mobile-checkout',
		);
	}

	/**
	 * Check if ecommerce plugin is active.
	 *
	 * @since  1.2602.1445
	 * @return bool Has ecommerce plugin.
	 */
	private static function has_ecommerce_plugin(): bool {
		return class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' );
	}

	/**
	 * Find checkout form issues.
	 *
	 * @since  1.2602.1445
	 * @return array Issues found.
	 */
	private static function find_checkout_issues(): array {
		$issues = array();

		// Check field count
		$field_issues = self::check_field_count();
		$issues = array_merge( $issues, $field_issues );

		// Check payment methods
		$payment_issues = self::check_payment_methods();
		$issues = array_merge( $issues, $payment_issues );

		// Check guest checkout
		$guest_issues = self::check_guest_checkout();
		$issues = array_merge( $issues, $guest_issues );

		// Check mobile payment options
		$mobile_payment_issues = self::check_mobile_payments();
		$issues = array_merge( $issues, $mobile_payment_issues );

		return array( 'all' => $issues );
	}

	/**
	 * Check checkout field count.
	 *
	 * @since  1.2602.1445
	 * @return array Field count issues.
	 */
	private static function check_field_count(): array {
		$issues = array();

		if ( class_exists( 'WooCommerce' ) ) {
			$fields = \WC()->countries->get_address_fields( '', 'billing_' );
			$required_count = 0;

			foreach ( $fields as $field ) {
				if ( ! empty( $field['required'] ) ) {
					$required_count++;
				}
			}

			if ( $required_count > 8 ) {
				$issues[] = array(
					'type'        => 'too-many-fields',
					'field_count' => $required_count,
					'recommended' => 8,
					'issue'       => 'Checkout has too many required fields for mobile',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check payment method availability.
	 *
	 * @since  1.2602.1445
	 * @return array Payment method issues.
	 */
	private static function check_payment_methods(): array {
		$issues = array();

		if ( class_exists( 'WooCommerce' ) ) {
			$gateways = \WC()->payment_gateways->get_available_payment_gateways();

			if ( count( $gateways ) < 2 ) {
				$issues[] = array(
					'type'  => 'limited-payment-options',
					'count' => count( $gateways ),
					'issue' => 'Limited payment options may reduce mobile conversions',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check guest checkout availability.
	 *
	 * @since  1.2602.1445
	 * @return array Guest checkout issues.
	 */
	private static function check_guest_checkout(): array {
		$issues = array();

		if ( class_exists( 'WooCommerce' ) ) {
			$guest_checkout = get_option( 'woocommerce_enable_guest_checkout', 'no' );

			if ( 'yes' !== $guest_checkout ) {
				$issues[] = array(
					'type'  => 'no-guest-checkout',
					'issue' => 'Guest checkout disabled (increases mobile friction)',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for mobile payment options.
	 *
	 * @since  1.2602.1445
	 * @return array Mobile payment issues.
	 */
	private static function check_mobile_payments(): array {
		$issues = array();

		if ( class_exists( 'WooCommerce' ) ) {
			$gateways = \WC()->payment_gateways->get_available_payment_gateways();
			$has_mobile_payment = false;

			$mobile_gateways = array( 'stripe', 'paypal', 'apple_pay', 'google_pay' );

			foreach ( $gateways as $gateway ) {
				$gateway_id = strtolower( $gateway->id );
				foreach ( $mobile_gateways as $mobile_gateway ) {
					if ( false !== strpos( $gateway_id, $mobile_gateway ) ) {
						$has_mobile_payment = true;
						break 2;
					}
				}
			}

			if ( ! $has_mobile_payment ) {
				$issues[] = array(
					'type'  => 'no-mobile-payments',
					'issue' => 'No mobile-optimized payment methods (Apple Pay, Google Pay)',
				);
			}
		}

		return $issues;
	}
}
