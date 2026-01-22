<?php declare(strict_types=1);
/**
 * Missing Product Schema Diagnostic
 *
 * Philosophy: SEO ecommerce - product schema enables rich snippets
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for missing product schema on WooCommerce.
 */
class Diagnostic_SEO_Missing_Product_Schema {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( class_exists( 'WooCommerce' ) ) {
			$products = wp_count_posts( 'product' );
			
			if ( $products->publish > 0 ) {
				return array(
					'id'          => 'seo-missing-product-schema',
					'title'       => 'Verify Product Schema Implementation',
					'description' => sprintf( '%d products detected. Ensure Product schema includes price, availability, reviews. Product schema shows price/availability in search results.', $products->publish ),
					'severity'    => 'medium',
					'category'    => 'seo',
					'kb_link'     => 'https://wpshadow.com/kb/optimize-product-schema/',
					'training_link' => 'https://wpshadow.com/training/woocommerce-seo/',
					'auto_fixable' => false,
					'threat_level' => 60,
				);
			}
		}
		
		return null;
	}
}
