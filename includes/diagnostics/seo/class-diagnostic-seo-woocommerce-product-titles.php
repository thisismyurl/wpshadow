<?php
declare(strict_types=1);
/**
 * WooCommerce Product Titles Diagnostic
 *
 * Philosophy: SEO ecommerce - optimize product titles for search
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check WooCommerce product title optimization.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_WooCommerce_Product_Titles extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$products = wc_get_products( array( 'limit' => 20 ) );
		
		$short = 0;
		foreach ( $products as $product ) {
			if ( strlen( $product->get_name() ) < 20 ) {
				$short++;
			}
		}
		
		if ( $short > 5 ) {
			return array(
				'id'          => 'seo-woocommerce-product-titles',
				'title'       => 'WooCommerce Product Titles Too Short',
				'description' => sprintf( '%d products have short titles (< 20 chars). Include: brand, model, key features. Example: "Nike Air Max 270 Men\'s Running Shoes - Black/White".', $short ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-product-titles/',
				'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
