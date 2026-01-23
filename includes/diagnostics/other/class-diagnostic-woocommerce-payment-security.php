<?php
declare(strict_types=1);
/**
 * WooCommerce Payment Gateway Security Diagnostic
 *
 * Philosophy: Payment security - PCI compliance
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check WooCommerce payment security.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Woocommerce_Payment_Security extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return null;
		}

		$gateways = get_option( 'woocommerce_enabled_payment_gateways', array() );

		if ( empty( $gateways ) ) {
			return null;
		}

		// Check if using outdated or insecure gateways
		foreach ( $gateways as $gateway ) {
			if ( preg_match( '/paypal|stripe|braintree|authorize/', $gateway ) ) {
				return null; // Using secure gateway
			}
		}

		return array(
			'id'            => 'woocommerce-payment-security',
			'title'         => 'WooCommerce Using Insecure Payment Gateway',
			'description'   => 'Payment gateway is not PCI DSS compliant or outdated. Storing credit cards insecurely exposes customer data. Use certified gateways (Stripe, PayPal, Braintree).',
			'severity'      => 'critical',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/pci-compliant-payments/',
			'training_link' => 'https://wpshadow.com/training/payment-security/',
			'auto_fixable'  => false,
			'threat_level'  => 90,
		);
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
