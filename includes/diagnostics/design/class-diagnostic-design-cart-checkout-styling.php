<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Cart/Checkout Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-cart-checkout-styling
 * Training: https://wpshadow.com/training/design-cart-checkout-styling
 */
class Diagnostic_Design_CART_CHECKOUT_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-cart-checkout-styling',
            'title' => __('Cart/Checkout Styling', 'wpshadow'),
            'description' => __('Validates e-commerce checkout styled professionally.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-cart-checkout-styling',
            'training_link' => 'https://wpshadow.com/training/design-cart-checkout-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}