<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Pagination_Parameter_Variation extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-pagination-variation', 'title' => __('Pagination Parameter Variation', 'wpshadow'), 'description' => __('Detects inconsistent pagination: ?page=2 vs ?p=2 vs pagination fragments creating duplicate crawl paths. Standardize pagination parameters to reduce waste.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/pagination-seo/', 'training_link' => 'https://wpshadow.com/training/pagination-optimization/', 'auto_fixable' => false, 'threat_level' => 5];
    }

}