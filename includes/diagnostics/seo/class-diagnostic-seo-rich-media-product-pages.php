<?php
declare(strict_types=1);
/**
 * Rich Media on Product Pages Diagnostic
 *
 * Philosophy: Multiple images and videos improve engagement
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Rich_Media_Product_Pages extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-rich-media-product-pages',
            'title' => 'Rich Media on Product Pages',
            'description' => 'Encourage multiple high-quality images and videos on product pages to improve engagement and conversions.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/product-media/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
