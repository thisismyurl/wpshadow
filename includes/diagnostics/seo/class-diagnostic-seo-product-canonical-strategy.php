<?php
declare(strict_types=1);
/**
 * Product Canonical Strategy Diagnostic
 *
 * Philosophy: Canonical variants to parent product
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Product_Canonical_Strategy extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-product-canonical-strategy',
            'title' => 'Product Canonical Strategy',
            'description' => 'Ensure product variations canonicalize to the parent product page to avoid duplicate indexation.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/product-canonicals/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}