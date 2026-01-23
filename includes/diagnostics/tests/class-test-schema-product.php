<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Product extends Diagnostic_Base {
    
    protected static $slug = 'test-schema-product';
    protected static $title = 'Product Schema Test';
    protected static $description = 'Tests for Product structured data (WooCommerce/eCommerce)';
    
    public static function check(?string $url = null, ?string $html = null): ?array {
        if ($html !== null) {
            return self::analyze_html($html, $url ?? 'provided-html');
        }
        
        $html = self::fetch_html($url ?? home_url('/'));
        if ($html === false) {
            return null;
        }
        
        return self::analyze_html($html, $url ?? home_url('/'));
    }
    
    protected static function analyze_html(string $html, string $checked_url): ?array {
        // Check if WooCommerce is active
        $is_woocommerce = class_exists('WooCommerce');
        
        // Check for product indicators
        $has_product_html = preg_match('/class=["\'][^"\']*product[^"\']*["\']|<div[^>]*itemprop=["\']product/i', $html);
        $has_price = preg_match('/<span[^>]*class=["\'][^"\']*price|itemprop=["\']price/i', $html);
        
        // Check for Product schema
        $has_product_schema = preg_match('/"@type"\s*:\s*"Product"/i', $html);
        
        // If looks like product page but no schema
        if (($is_woocommerce || $has_product_html) && $has_price && !$has_product_schema) {
            return [
                'id' => 'schema-product-missing',
                'title' => 'Product Schema Missing',
                'description' => 'Product page detected but no Product structured data found. Product schema enables rich results in search (price, availability, reviews).',
                'color' => '#2196f3',
                'bg_color' => '#e3f2fd',
                'kb_link' => 'https://wpshadow.com/kb/product-schema/',
                'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
                'auto_fixable' => false,
                'threat_level' => 40,
                'module' => 'SEO',
                'priority' => 2,
                'meta' => [
                    'is_woocommerce' => $is_woocommerce,
                    'has_product_html' => $has_product_html,
                    'has_price' => $has_price,
                    'has_schema' => $has_product_schema,
                    'checked_url' => $checked_url,
                ],
            ];
        }
        
        return null;
    }
    
    protected static function fetch_html(string $url) {
        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
        return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
    }
    
    public static function get_name(): string {
        return __('Product Schema', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks for Product structured data (eCommerce).', 'wpshadow');
    }
}
