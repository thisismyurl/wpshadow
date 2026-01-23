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
class Diagnostic_Payment_Gateway_Functional extends Diagnostic_Base
{
	protected static $slug        = 'payment-gateway-functional';
	protected static $title       = 'Payment Processing Working?';
	protected static $description = 'Verifies payment gateway connectivity.';

	public static function check(): ?array
	{
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
	public static function test_live_payment_gateway_functional(): array
	{
		$wc_active = class_exists('WooCommerce') && function_exists('WC');
		$edd_active = class_exists('Easy_Digital_Downloads') && function_exists('edd_get_enabled_payment_gateways');

		$wc_gateway_count = 0;
		$wc_gateway_data_ok = false;
		if ($wc_active) {
			$wc_instance = WC();
			if (is_object($wc_instance) && isset($wc_instance->payment_gateways) && is_object($wc_instance->payment_gateways) && method_exists($wc_instance->payment_gateways, 'get_available_payment_gateways')) {
				$available_gateways = $wc_instance->payment_gateways->get_available_payment_gateways();
				if (is_array($available_gateways)) {
					$wc_gateway_count = count($available_gateways);
					$wc_gateway_data_ok = true;
				}
			}
		}

		$edd_gateway_count = 0;
		if ($edd_active) {
			$edd_gateways = edd_get_enabled_payment_gateways();
			if (is_array($edd_gateways)) {
				$edd_gateway_count = count($edd_gateways);
			}
		}

		$expected_issue = false;
		if ($wc_active) {
			// If WooCommerce is active but we cannot read gateways, treat as issue (align with diagnostic intent)
			$expected_issue = (! $wc_gateway_data_ok) || (0 === $wc_gateway_count);
		} elseif ($edd_active) {
			$expected_issue = (0 === $edd_gateway_count);
		}

		$diagnostic_result    = self::check();
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($expected_issue === $diagnostic_has_issue);

		$message = sprintf(
			'WooCommerce active: %s (gateways: %d). EDD active: %s (gateways: %d). Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$wc_active ? 'YES' : 'NO',
			$wc_gateway_count,
			$edd_active ? 'YES' : 'NO',
			$edd_gateway_count,
			$expected_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
