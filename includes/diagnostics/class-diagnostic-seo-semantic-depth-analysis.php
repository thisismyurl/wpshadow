<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Semantic_Depth_Analysis {
    public static function check() {
        return ['id' => 'seo-semantic-depth', 'title' => __('Semantic Depth Analysis', 'wpshadow'), 'description' => __('Measures concept interconnection and how deeply content explores topic relationships. AI skims surfaces; experts show how concepts connect. Low depth = low authority.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/topical-authority/', 'training_link' => 'https://wpshadow.com/training/topic-clusters/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
