<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Robots_Txt_Efficiency extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-robots-txt', 'title' => __('Robots.txt Crawl Efficiency', 'wpshadow'), 'description' => __('Audits robots.txt for unnecessary disallows, wildcards blocking valuable content, or overly permissive rules wasting crawl budget on unimportant pages.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/robots-txt/', 'training_link' => 'https://wpshadow.com/training/crawl-control/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
