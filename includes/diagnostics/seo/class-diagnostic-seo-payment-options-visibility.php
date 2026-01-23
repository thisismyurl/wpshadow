<?php
declare(strict_types=1);
/**
 * Payment Options Visibility Diagnostic
 *
 * Philosophy: Payment options reduce checkout anxiety
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Payment_Options_Visibility extends Diagnostic_Base {
    public static function check(): ?array {
        if (class_exists('WooCommerce')) {
            return [
                'id' => 'seo-payment-options-visibility',
                'title' => 'Payment Methods Display',
                'description' => 'Display accepted payment methods prominently: cards, PayPal, wallets, BNPL.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/payment-visibility/',
                'training_link' => 'https://wpshadow.com/training/checkout-trust/',
                'auto_fixable' => false,
                'threat_level' => 30,
            ];
        }
        return null;
    }

}