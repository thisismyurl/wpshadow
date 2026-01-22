<?php declare(strict_types=1);
/**
 * Orphaned URLs in Sitemap Diagnostic
 *
 * Philosophy: Prioritize URLs linked internally
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Orphaned_URLs_In_Sitemap {
    public static function check() {
        return [
            'id' => 'seo-orphaned-urls-in-sitemap',
            'title' => 'Orphaned URLs in Sitemap',
            'description' => 'Avoid including URLs in sitemaps that are not linked internally; focus crawl on discoverable content.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/orphaned-urls-sitemap/',
            'training_link' => 'https://wpshadow.com/training/internal-linking/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
