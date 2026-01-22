<?php
declare(strict_types=1);
/**
 * Product Q&A Section Diagnostic
 *
 * Philosophy: Q&A reduces pre-purchase questions
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Product_QA_Section extends Diagnostic_Base {
    public static function check(): ?array {
        if (class_exists('WooCommerce')) {
            return [
                'id' => 'seo-product-qa-section',
                'title' => 'Product Q&A Section',
                'description' => 'Enable customer Q&A on product pages. User-generated answers build trust.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/product-qa/',
                'training_link' => 'https://wpshadow.com/training/ugc-ecommerce/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
        }
        return null;
    }
}
