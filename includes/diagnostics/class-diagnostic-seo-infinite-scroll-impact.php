<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Infinite_Scroll_Impact {
    public static function check() {
        return ['id' => 'seo-infinite-scroll', 'title' => __('Infinite Scroll Crawl Impact', 'wpshadow'), 'description' => __('Detects infinite scroll implementations that prevent Google from discovering pagination endpoints. Crawlers get stuck at first page, missing indexed content.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/pagination/', 'training_link' => 'https://wpshadow.com/training/pagination-strategy/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
