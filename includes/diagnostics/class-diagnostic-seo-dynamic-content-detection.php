<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Dynamic_Content_Detection {
    public static function check() {
        return ['id' => 'seo-dynamic-content', 'title' => __('Dynamic Content Crawlability', 'wpshadow'), 'description' => __('Detects content loaded dynamically (AJAX, client-side rendering, lazy loading). If important content only visible after JS execution, Google misses crawl opportunities.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/javascript-seo/', 'training_link' => 'https://wpshadow.com/training/js-optimization/', 'auto_fixable' => false, 'threat_level' => 8];
    }
}
