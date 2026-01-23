<?php
declare(strict_types=1);
/**
 * WooCommerce Product Completeness Diagnostic
 *
 * Philosophy: Rich product data improves visibility
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_WooCommerce_Product_Completeness extends Diagnostic_Base {
    /**
     * Sample a few products for missing key fields (sku/brand/etc.).
     *
     * @return array|null
     */
    public static function check(): ?array {
        if (!class_exists('WC_Product')) {
            return null;
        }
        $missing = 0;
        if (function_exists('wc_get_products')) {
            $products = wc_get_products(['limit' => 10, 'status' => 'publish']);
            foreach ($products as $product) {
                $sku = $product->get_sku();
                $brand = get_post_meta($product->get_id(), 'brand', true);
                $gtin = get_post_meta($product->get_id(), 'gtin', true);
                $price = $product->get_price();
                $currency = get_option('woocommerce_currency');
                if (empty($sku) || empty($brand) || empty($price) || empty($currency)) {
                    $missing++;
                }
            }
        }
        if ($missing > 0) {
            return [
                'id' => 'seo-woocommerce-product-completeness',
                'title' => 'WooCommerce Products Missing Key Fields',
                'description' => sprintf('%d sampled products missing one or more key fields (SKU, brand, price, currency).', $missing),
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/woocommerce-product-seo/',
                'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
                'auto_fixable' => false,
                'threat_level' => 40,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO WooCommerce Product Completeness
	 * Slug: -seo-woocommerce-product-completeness
	 * File: class-diagnostic-seo-woocommerce-product-completeness.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO WooCommerce Product Completeness
	 * Slug: -seo-woocommerce-product-completeness
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__seo_woocommerce_product_completeness(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}
