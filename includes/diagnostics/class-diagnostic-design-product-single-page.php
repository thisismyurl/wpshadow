<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: WooCommerce Product Page
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-product-single-page
 * Training: https://wpshadow.com/training/design-product-single-page
 */
class Diagnostic_Design_PRODUCT_SINGLE_PAGE {
    public static function check() {
        return [
            'id' => 'design-product-single-page',
            'title' => __('WooCommerce Product Page', 'wpshadow'),
            'description' => __('Checks product page layout, image gallery, styling.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-product-single-page',
            'training_link' => 'https://wpshadow.com/training/design-product-single-page',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
