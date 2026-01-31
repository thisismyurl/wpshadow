<?php
/**
 * Divi Builder Pro Woocommerce Integration Diagnostic
 *
 * Divi Builder Pro Woocommerce Integration issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.811.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Pro Woocommerce Integration Diagnostic Class
 *
 * @since 1.811.0000
 */
class Diagnostic_DiviBuilderProWoocommerceIntegration extends Diagnostic_Base {

	protected static $slug = 'divi-builder-pro-woocommerce-integration';
	protected static $title = 'Divi Builder Pro Woocommerce Integration';
	protected static $description = 'Divi Builder Pro Woocommerce Integration issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Product builder modules enabled
		$product_modules = get_option( 'divi_woocommerce_product_modules', false );
		if ( ! $product_modules ) {
			$issues[] = 'Product builder modules disabled';
		}

		// Check 2: Cart integration configured
		$cart_integration = get_option( 'divi_woocommerce_cart_integration', false );
		if ( ! $cart_integration ) {
			$issues[] = 'Cart integration not configured';
		}

		// Check 3: Checkout page styling
		$checkout_styling = get_option( 'divi_woocommerce_checkout_styling', false );
		if ( ! $checkout_styling ) {
			$issues[] = 'Checkout styling not applied';
		}

		// Check 4: Product filters enabled
		$product_filters = get_option( 'divi_woocommerce_product_filters', false );
		if ( ! $product_filters ) {
			$issues[] = 'Product filters disabled';
		}

		// Check 5: Custom templates configured
		$custom_templates = get_option( 'divi_woocommerce_custom_templates', false );
		if ( ! $custom_templates ) {
			$issues[] = 'Custom templates not configured';
		}

		// Check 6: AJAX cart enabled
		$ajax_cart = get_option( 'divi_woocommerce_ajax_cart', false );
		if ( ! $ajax_cart ) {
			$issues[] = 'AJAX cart disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Divi Builder WooCommerce integration issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-pro-woocommerce-integration',
			);
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
