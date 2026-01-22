<?php declare(strict_types=1);
/**
 * Product Offers Schema Diagnostic
 *
 * Philosophy: Complete Product structured data richness
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Product_Offers_Schema {
    public static function check() {
        return [
            'id' => 'seo-product-offers-schema',
            'title' => 'Product Offers Schema Completeness',
            'description' => 'Ensure Product structured data includes complete offers (price, availability, priceCurrency).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/product-offers-schema/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
