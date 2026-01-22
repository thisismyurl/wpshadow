<?php declare(strict_types=1);
/**
 * Guest Checkout Availability Diagnostic
 *
 * Philosophy: Forced accounts hurt conversion
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Guest_Checkout_Availability {
    public static function check() {
        if (class_exists('WooCommerce')) {
            $guest_checkout = get_option('woocommerce_enable_guest_checkout');
            if ($guest_checkout !== 'yes') {
                return [
                    'id' => 'seo-guest-checkout-availability',
                    'title' => 'Guest Checkout Not Enabled',
                    'description' => 'Enable guest checkout. Forced account creation reduces conversion rates.',
                    'severity' => 'high',
                    'category' => 'seo',
                    'kb_link' => 'https://wpshadow.com/kb/guest-checkout/',
                    'training_link' => 'https://wpshadow.com/training/checkout-optimization/',
                    'auto_fixable' => false,
                    'threat_level' => 60,
                ];
            }
        }
        return null;
    }
}
