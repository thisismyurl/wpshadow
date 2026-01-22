<?php declare(strict_types=1);
/**
 * Sitemap Child Status Diagnostic
 *
 * Philosophy: Ensure child sitemaps return 200 OK
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Sitemap_Child_Status {
    public static function check() {
        return [
            'id' => 'seo-sitemap-child-status',
            'title' => 'Child Sitemaps Status',
            'description' => 'Validate that all child sitemaps referenced in the sitemap index return HTTP 200.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/sitemap-child-status/',
            'training_link' => 'https://wpshadow.com/training/sitemaps-at-scale/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
