<?php declare(strict_types=1);
/**
 * Product Comparison Tables Diagnostic
 *
 * Philosophy: Comparisons help purchase decisions
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Product_Comparison_Tables {
    public static function check() {
        if (class_exists('WooCommerce')) {
            return [
                'id' => 'seo-product-comparison-tables',
                'title' => 'Product Comparison Tables',
                'description' => 'Add comparison tables with features, prices, specs to aid purchase decisions.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/product-comparisons/',
                'training_link' => 'https://wpshadow.com/training/ecommerce-content/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
        }
        return null;
    }
}
