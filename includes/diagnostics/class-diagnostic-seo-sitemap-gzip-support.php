<?php declare(strict_types=1);
/**
 * Sitemap Gzip Support Diagnostic
 *
 * Philosophy: Compress large sitemaps for efficiency
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Sitemap_Gzip_Support {
    public static function check() {
        return [
            'id' => 'seo-sitemap-gzip-support',
            'title' => 'Sitemap Gzip Support',
            'description' => 'Large sites benefit from gzipped sitemaps to reduce bandwidth and improve crawling efficiency.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/gzip-sitemaps/',
            'training_link' => 'https://wpshadow.com/training/performance-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
