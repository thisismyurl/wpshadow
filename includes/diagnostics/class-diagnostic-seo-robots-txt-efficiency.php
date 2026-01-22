<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_Robots_Txt_Efficiency {
    public static function check() {
        return ['id' => 'seo-robots-txt', 'title' => __('Robots.txt Crawl Efficiency', 'wpshadow'), 'description' => __('Audits robots.txt for unnecessary disallows, wildcards blocking valuable content, or overly permissive rules wasting crawl budget on unimportant pages.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/robots-txt/', 'training_link' => 'https://wpshadow.com/training/crawl-control/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
