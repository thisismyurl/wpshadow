<?php
/**
 * Checkout Funnel Friction Analysis (WooCommerce) Diagnostic
 *
 * Identifies friction points in WooCommerce checkout causing abandonment.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkout Funnel Friction Analysis Class
 *
 * Tests WooCommerce checkout friction.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Checkout_Funnel_Friction_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'checkout-funnel-friction-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Checkout Funnel Friction Analysis (WooCommerce)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies friction points in WooCommerce checkout causing abandonment';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null; // Not applicable.
		}

		$checkout_check = self::check_checkout_friction();
		
		if ( $checkout_check['has_friction'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $checkout_check['friction_points'] ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/checkout-funnel-friction-analysis',
				'meta'         => array(
					'guest_checkout_enabled'   => $checkout_check['guest_checkout_enabled'],
					'required_fields_count'    => $checkout_check['required_fields_count'],
					'payment_methods_count'    => $checkout_check['payment_methods_count'],
				),
			);
		}

		return null;
	}

	/**
	 * Check checkout friction points.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_checkout_friction() {
		$check = array(
			'has_friction'             => false,
			'friction_points'          => array(),
			'guest_checkout_enabled'   => false,
			'required_fields_count'    => 0,
			'payment_methods_count'    => 0,
		);

		// Check guest checkout setting.
		$check['guest_checkout_enabled'] = ( 'yes' === get_option( 'woocommerce_enable_guest_checkout' ) );

		if ( ! $check['guest_checkout_enabled'] ) {
			$check['has_friction'] = true;
			$check['friction_points'][] = __( 'Guest checkout disabled (forced account creation reduces conversion by 30%)', 'wpshadow' );
		}

		// Count available payment gateways.
		if ( class_exists( 'WC_Payment_Gateways' ) ) {
			$payment_gateways = \WC_Payment_Gateways::instance();
			$available_gateways = $payment_gateways->get_available_payment_gateways();
			$check['payment_methods_count'] = count( $available_gateways );

			if ( $check['payment_methods_count'] < 2 ) {
				$check['has_friction'] = true;
				$check['friction_points'][] = sprintf(
					/* translators: %d: number of payment methods */
					__( 'Only %d payment method available (limited payment options reduce conversion)', 'wpshadow' ),
					$check['payment_methods_count']
				);
			}
		}

		// Estimate required fields count.
		$checkout_fields = array();
		
		if ( function_exists( 'WC' ) && isset( WC()->countries ) ) {
			$checkout_fields = WC()->countries->get_default_address_fields();
		}

		// Count required fields.
		$required_count = 0;
		foreach ( $checkout_fields as $field_key => $field_data ) {
			if ( isset( $field_data['required'] ) && $field_data['required'] ) {
				$required_count++;
			}
		}

		// Add billing email, account password if registration required.
		$required_count += 1; // Email always required.
		
		if ( ! $check['guest_checkout_enabled'] ) {
			$required_count += 2; // Password fields.
		}

		$check['required_fields_count'] = $required_count;

		if ( $check['required_fields_count'] > 10 ) {
			$check['has_friction'] = true;
			$check['friction_points'][] = sprintf(
				/* translators: %d: number of required fields */
				__( '%d required checkout fields (each additional field reduces conversion)', 'wpshadow' ),
				$check['required_fields_count']
			);
		}

		return $check;
	}
}
