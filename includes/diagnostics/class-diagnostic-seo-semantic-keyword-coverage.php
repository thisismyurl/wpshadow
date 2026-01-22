<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Semantic_Keyword_Coverage {
    public static function check() {
        return ['id' => 'seo-semantic-keyword-gap', 'title' => __('Semantic Keyword Coverage Gap', 'wpshadow'), 'description' => __('Analyzes synonym and semantic variations competitors rank for. Missing "alternative keywords" suggests incomplete semantic content optimization.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/semantic-seo/', 'training_link' => 'https://wpshadow.com/training/topical-depth/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
