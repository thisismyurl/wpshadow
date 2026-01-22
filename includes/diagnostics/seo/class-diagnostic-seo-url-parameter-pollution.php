<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_URL_Parameter_Pollution extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-param-pollution', 'title' => __('URL Parameter Pollution Detection', 'wpshadow'), 'description' => __('Detects excessive URL parameters creating unique URLs for same content. ?sort=asc vs sort=desc shouldn\'t create crawl bloat. Consolidate parameters to save budget.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/url-structure/', 'training_link' => 'https://wpshadow.com/training/canonical-tags/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
