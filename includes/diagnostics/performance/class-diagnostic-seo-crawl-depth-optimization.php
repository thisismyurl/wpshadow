<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Crawl_Depth_Optimization extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-crawl-depth', 'title' => __('Crawl Depth Optimization', 'wpshadow'), 'description' => __('Analyzes click depth to reach valuable content. If key articles require 10+ clicks from homepage, Google crawls less efficiently. Flatten navigation hierarchy.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/site-architecture/', 'training_link' => 'https://wpshadow.com/training/information-hierarchy/', 'auto_fixable' => false, 'threat_level' => 7];
    }

}