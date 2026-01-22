<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: WooCommerce Product Loop
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-product-loop-woocommerce
 * Training: https://wpshadow.com/training/design-product-loop-woocommerce
 */
class Diagnostic_Design_PRODUCT_LOOP_WOOCOMMERCE {
    public static function check() {
        return [
            'id' => 'design-product-loop-woocommerce',
            'title' => __('WooCommerce Product Loop', 'wpshadow'),
            'description' => __('Verifies product grids responsive, styled correctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-product-loop-woocommerce',
            'training_link' => 'https://wpshadow.com/training/design-product-loop-woocommerce',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
