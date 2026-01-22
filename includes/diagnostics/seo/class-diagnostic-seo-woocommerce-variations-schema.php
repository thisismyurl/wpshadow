<?php
declare(strict_types=1);
/**
 * WooCommerce Variations Schema Diagnostic
 *
 * Philosophy: Ensure structured data for variable products
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_WooCommerce_Variations_Schema extends Diagnostic_Base {
    /**
     * Advisory: verify structured data covers variations.
     *
     * @return array|null
     */
    public static function check(): ?array {
        if (!class_exists('WC_Product')) {
            return null;
        }
        return [
            'id' => 'seo-woocommerce-variations-schema',
            'title' => 'Structured Data for Variable Products',
            'description' => 'Ensure variable products output proper structured data for variations (prices, availability, attributes).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/woocommerce-structured-data/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
