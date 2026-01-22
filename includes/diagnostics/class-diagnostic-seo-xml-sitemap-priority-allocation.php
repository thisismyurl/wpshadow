<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_SEO_XML_Sitemap_Priority_Allocation {
    public static function check() {
        return ['id' => 'seo-sitemap-priority', 'title' => __('XML Sitemap Priority Allocation', 'wpshadow'), 'description' => __('Audits sitemap priorities. If every page has priority 1.0, Google ignores signals. Proper priority distribution (0.3-1.0) guides crawl budget to important pages.', 'wpshadow'), 'severity' => 'medium', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/xml-sitemaps/', 'training_link' => 'https://wpshadow.com/training/sitemap-strategy/', 'auto_fixable' => false, 'threat_level' => 5];
    }
}
