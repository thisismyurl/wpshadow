<?php
/**
 * Woocommerce Composite Products Pricing Diagnostic
 *
 * Woocommerce Composite Products Pricing issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.671.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Composite Products Pricing Diagnostic Class
 *
 * @since 1.671.0000
 */
class Diagnostic_WoocommerceCompositeProductsPricing extends Diagnostic_Base {

	protected static $slug = 'woocommerce-composite-products-pricing';
	protected static $title = 'Woocommerce Composite Products Pricing';
	protected static $description = 'Woocommerce Composite Products Pricing issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify composite pricing enabled
		$composite_pricing = get_option( 'wc_composite_pricing_enabled', false );
		if ( ! $composite_pricing ) {
			$issues[] = __( 'Composite product pricing not enabled', 'wpshadow' );
		}

		// Check 2: Check component price calculation
		$component_calc = get_option( 'wc_composite_component_pricing', false );
		if ( ! $component_calc ) {
			$issues[] = __( 'Component price calculation not configured', 'wpshadow' );
		}

		// Check 3: Verify discount rules
		$discount_rules = get_option( 'wc_composite_discount_rules', false );
		if ( ! $discount_rules ) {
			$issues[] = __( 'Composite discount rules not configured', 'wpshadow' );
		}

		// Check 4: Check pricing cache
		$pricing_cache = get_transient( 'wc_composite_pricing_cache' );
		if ( false === $pricing_cache ) {
			$issues[] = __( 'Composite pricing caching not active', 'wpshadow' );
		}

		// Check 5: Verify product variant pricing
		$variant_pricing = get_option( 'wc_composite_variant_pricing', false );
		if ( ! $variant_pricing ) {
			$issues[] = __( 'Product variant pricing not configured', 'wpshadow' );
		}

		// Check 6: Check tax calculation
		$tax_calc = get_option( 'wc_composite_tax_calculation', false );
		if ( ! $tax_calc ) {
			$issues[] = __( 'Composite product tax calculation not configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WooCommerce composite product pricing issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-composite-products-pricing',
			);
		}

		return null;
	}
}

	}
}
