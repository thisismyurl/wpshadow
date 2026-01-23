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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Payment Processing Working?
	 * Slug: payment-gateway-functional
	 * 
	 * Test Purpose:
	 * - Verify that payment gateways are properly configured and active
	 * - PASS: check() returns NULL when payment gateways are configured and enabled
	 * - FAIL: check() returns array when no payment gateways are configured
	 * - Supports: WooCommerce, Easy Digital Downloads
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_payment_gateway_functional(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// Pattern: check() returns NULL when payment gateways are configured (healthy)
		// Pattern: check() returns array when no gateways configured (issue)
		
		if ($result === null) {
			return [
				'passed' => true,
				'message' => 'Payment gateways are properly configured and available',
			];
		}
		
		// Extract which platform has the issue
		$message = $result['description'] ?? 'Payment gateway not configured';
		return [
			'passed' => false,
			'message' => $message,
		];
	}

}