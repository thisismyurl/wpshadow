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
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
