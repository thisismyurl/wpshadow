<?php
declare(strict_types=1);
/**
 * Breadcrumbs on Product Pages Diagnostic
 *
 * Philosophy: Navigation clarity and schema signals
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Breadcrumbs_Product_Pages extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-breadcrumbs-product-pages',
            'title' => 'Breadcrumbs on Product/Category Pages',
            'description' => 'Ensure breadcrumbs are present on product and category pages with BreadcrumbList schema.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/breadcrumbs-ecommerce/',
            'training_link' => 'https://wpshadow.com/training/ecommerce-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
