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

}