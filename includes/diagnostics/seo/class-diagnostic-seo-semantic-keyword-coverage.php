<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Semantic_Keyword_Coverage extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-semantic-keyword-gap', 'title' => __('Semantic Keyword Coverage Gap', 'wpshadow'), 'description' => __('Analyzes synonym and semantic variations competitors rank for. Missing "alternative keywords" suggests incomplete semantic content optimization.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/semantic-seo/', 'training_link' => 'https://wpshadow.com/training/topical-depth/', 'auto_fixable' => false, 'threat_level' => 6];
    }

}