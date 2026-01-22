<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Keyword_Coverage_Gap {
    public static function check() {
        return ['id' => 'seo-keyword-coverage-gap', 'title' => __('Keyword Coverage Gap vs Competitors', 'wpshadow'), 'description' => __('Identifies keywords competitors rank for that you don\'t. Analyzes keyword cluster completeness. Missing long-tail variations = lost traffic.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/keyword-strategy/', 'training_link' => 'https://wpshadow.com/training/keyword-research/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
