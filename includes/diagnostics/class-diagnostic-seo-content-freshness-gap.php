<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Content_Freshness_Gap {
    public static function check() {
        return ['id' => 'seo-freshness-gap', 'title' => __('Content Freshness Gap vs Competitors', 'wpshadow'), 'description' => __('Compares publication/update dates. If competitors update quarterly and you update yearly, freshness signals lag behind. Age matters for trending topics.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/content-freshness/', 'training_link' => 'https://wpshadow.com/training/update-strategy/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
