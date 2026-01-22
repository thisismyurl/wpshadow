<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Topic_Cluster_Gap extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-topic-cluster-gap', 'title' => __('Topic Cluster Completeness Gap', 'wpshadow'), 'description' => __('Analyzes if your content covers related topics in clusters. Competitors covering "SEO basics, advanced SEO, SEO tools" while you only have "SEO basics" = cluster gap.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/topic-clusters/', 'training_link' => 'https://wpshadow.com/training/topical-authority/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
