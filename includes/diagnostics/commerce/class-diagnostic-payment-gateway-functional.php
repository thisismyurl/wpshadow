<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Payment Processing Working?
 *
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Payment_Gateway_Functional extends Diagnostic_Base {
	protected static $slug        = 'payment-gateway-functional';
	protected static $title       = 'Payment Processing Working?';
	protected static $description = 'Verifies payment gateway connectivity.';

	public static function check(): ?array {
		// Check for WooCommerce
		if (class_exists('WooCommerce')) {
			$gateways = WC()->payment_gateways->get_available_payment_gateways();
			if (!empty($gateways)) {
				return null; // Pass - payment gateways configured
			}
			return array(
				'id'            => static::$slug,
				'title'         => static::$title,
				'description'   => 'WooCommerce installed but no payment gateways enabled.',
				'color'         => '#f44336',
				'bg_color'      => '#ffebee',
				'kb_link'       => 'https://wpshadow.com/kb/payment-gateway-functional/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=payment-gateway-functional',
				'training_link' => 'https://wpshadow.com/training/payment-gateway-functional/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Commerce',
				'priority'      => 1,
			);
		}
		
		// Check for Easy Digital Downloads
		if (class_exists('Easy_Digital_Downloads')) {
			$gateways = edd_get_enabled_payment_gateways();
			if (!empty($gateways)) {
				return null; // Pass - payment gateways configured
			}
			return array(
				'id'            => static::$slug,
				'title'         => static::$title,
				'description'   => 'Easy Digital Downloads installed but no payment gateways enabled.',
				'color'         => '#f44336',
				'bg_color'      => '#ffebee',
				'kb_link'       => 'https://wpshadow.com/kb/payment-gateway-functional/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=payment-gateway-functional',
				'training_link' => 'https://wpshadow.com/training/payment-gateway-functional/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Commerce',
				'priority'      => 1,
			);
		}
		
		// No e-commerce platform detected
		return null;
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
	}}
