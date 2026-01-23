<?php
declare(strict_types=1);
/**
 * Sitemap Lastmod Coverage Diagnostic
 *
 * Philosophy: Keep sitemaps fresh with lastmod dates
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Sitemap_Lastmod_Coverage extends Diagnostic_Base {
    /**
     * Advisory: check that sitemaps include lastmod (heuristic only).
     *
     * @return array|null
     */
    public static function check(): ?array {
        // Light advisory; full parsing deferred to future implementation
        return [
            'id' => 'seo-sitemap-lastmod-coverage',
            'title' => 'Sitemap Lastmod Coverage',
            'description' => 'Ensure sitemaps include accurate lastmod dates across all major sections (posts, pages, products).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/sitemap-lastmod/',
            'training_link' => 'https://wpshadow.com/training/sitemaps-maintenance/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }

}