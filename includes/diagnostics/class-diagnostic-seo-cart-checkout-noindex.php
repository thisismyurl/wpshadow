<?php declare(strict_types=1);
/**
 * Cart Checkout Noindex Diagnostic
 *
 * Philosophy: Block indexation of transactional pages
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Cart_Checkout_Noindex {
    public static function check() {
        return [
            'id' => 'seo-cart-checkout-noindex',
            'title' => 'Cart/Checkout Noindex & Nofollow',
            'description' => 'Ensure cart and checkout pages are noindex and nofollow to prevent indexation of transactional flows.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/cart-checkout-noindex/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
