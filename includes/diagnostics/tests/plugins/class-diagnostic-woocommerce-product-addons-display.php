<?php
/**
 * Woocommerce Product Addons Display Diagnostic
 *
 * Woocommerce Product Addons Display issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.646.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Product Addons Display Diagnostic Class
 *
 * @since 1.646.0000
 */
class Diagnostic_WoocommerceProductAddonsDisplay extends Diagnostic_Base {

	protected static $slug = 'woocommerce-product-addons-display';
	protected static $title = 'Woocommerce Product Addons Display';
	protected static $description = 'Woocommerce Product Addons Display issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Product_Addons' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Add-ons display mode
		$display_mode = get_option( 'wc_product_addons_display_mode', '' );
		if ( empty( $display_mode ) ) {
			$issues[] = 'Product add-ons display mode not configured';
		}
		
		// Check 2: Frontend validation
		$frontend_validation = get_option( 'wc_product_addons_frontend_validation', 0 );
		if ( ! $frontend_validation ) {
			$issues[] = 'Frontend validation not enabled';
		}
		
		// Check 3: Pricing display
		$price_display = get_option( 'wc_product_addons_price_display', '' );
		if ( empty( $price_display ) ) {
			$issues[] = 'Price display for add-ons not configured';
		}
		
		// Check 4: Conditional logic
		$conditional_logic = get_option( 'wc_product_addons_conditional_logic', 0 );
		if ( ! $conditional_logic ) {
			$issues[] = 'Conditional logic not enabled';
		}
		
		// Check 5: Add-on caching
		$addons_cache = get_option( 'wc_product_addons_cache', 0 );
		if ( ! $addons_cache ) {
			$issues[] = 'Product add-ons cache not enabled';
		}
		
		// Check 6: Template overrides
		$template_overrides = get_option( 'wc_product_addons_template_overrides', 0 );
		if ( $template_overrides ) {
			$issues[] = 'Template overrides enabled (may cause display issues)';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WooCommerce Product Add-ons display issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-product-addons-display',
			);
		}
		
		return null;
	}
}
