<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Noindex_Effectiveness {
    public static function check() {
        return ['id' => 'seo-noindex-effectiveness', 'title' => __('Noindex Tag Effectiveness', 'wpshadow'), 'description' => __('Verifies noindex implementation prevents page indexing (check Search Console). Missing noindex on archive pages, admin, low-value pages wastes crawl budget.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/indexation-control/', 'training_link' => 'https://wpshadow.com/training/robots-meta-tags/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
