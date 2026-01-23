<?php
declare(strict_types=1);
/**
 * Filter Usability Ecommerce Diagnostic
 *
 * Philosophy: Good filters help users find products
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Filter_Usability_Ecommerce extends Diagnostic_Base {
    public static function check(): ?array {
        if (class_exists('WooCommerce')) {
            return [
                'id' => 'seo-filter-usability-ecommerce',
                'title' => 'Product Filter Usability',
                'description' => 'Optimize product filters: intuitive controls, clear counts, mobile-friendly.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/product-filters/',
                'training_link' => 'https://wpshadow.com/training/ecommerce-ux/',
                'auto_fixable' => false,
                'threat_level' => 35,
            ];
        }
        return null;
    }

}