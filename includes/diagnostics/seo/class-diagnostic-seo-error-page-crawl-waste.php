<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Error_Page_Crawl_Waste extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-error-pages', 'title' => __('404/Error Page Crawl Waste Detection', 'wpshadow'), 'description' => __('Detects 404s being crawled repeatedly. Crawlers waste budget on non-existent URLs. Redirect 404s to relevant pages or noindex them to save budget.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/broken-links/', 'training_link' => 'https://wpshadow.com/training/error-handling/', 'auto_fixable' => false, 'threat_level' => 5];
    }

}