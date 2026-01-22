<?php declare(strict_types=1);
/**
 * Price Availability Freshness Diagnostic
 *
 * Philosophy: Keep e-commerce data fresh
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Price_Availability_Freshness {
    public static function check() {
        return [
            'id' => 'seo-price-availability-freshness',
            'title' => 'Price & Availability Freshness',
            'description' => 'Ensure product prices and availability are kept current to avoid stale data in search results.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/price-freshness/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
