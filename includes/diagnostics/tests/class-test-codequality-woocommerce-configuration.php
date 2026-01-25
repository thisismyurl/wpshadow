<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: WooCommerce Configuration (Code Quality)
 *
 * Checks if WooCommerce is properly configured
 * Philosophy: Show value (#9) - proper config prevents errors
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_CodeQuality_WooCommerceConfiguration extends Diagnostic_Base {


	public static function check(): ?array {
		// Check if WooCommerce is installed and active
		if ( ! function_exists( 'is_woocommerce' ) ) {
			// WooCommerce not active, nothing to check
			return null;
		}

		// Check if shop page is configured
		$shop_page_id = wc_get_page_id( 'shop' );

		if ( $shop_page_id === -1 ) {
			return array(
				'id'           => 'woocommerce-configuration',
				'title'        => __( 'WooCommerce shop page not configured', 'wpshadow' ),
				'description'  => __( 'Set up a shop page in WooCommerce settings to display products properly.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
			);
		}

		return null;
	}

	public static function test_live_woocommerce_configuration(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'WooCommerce configuration is correct', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
