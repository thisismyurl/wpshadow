<?php declare(strict_types=1);
/**
 * Shipping Calculator Visibility Diagnostic
 *
 * Philosophy: Transparent shipping builds trust
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Shipping_Calculator_Visibility {
    public static function check() {
        if (class_exists('WooCommerce')) {
            return [
                'id' => 'seo-shipping-calculator-visibility',
                'title' => 'Shipping Cost Transparency',
                'description' => 'Display shipping calculator on product pages so users know costs upfront.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/shipping-transparency/',
                'training_link' => 'https://wpshadow.com/training/checkout-optimization/',
                'auto_fixable' => false,
                'threat_level' => 30,
            ];
        }
        return null;
    }
}
