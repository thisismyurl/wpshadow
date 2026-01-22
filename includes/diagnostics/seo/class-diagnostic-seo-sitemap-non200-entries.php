<?php
declare(strict_types=1);
/**
 * Sitemap Non-200 Entries Diagnostic
 *
 * Philosophy: Avoid listing broken URLs in sitemaps
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Sitemap_Non200_Entries extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-sitemap-non200-entries',
            'title' => 'Sitemap Contains Non-200 URLs',
            'description' => 'Ensure URLs listed in sitemaps return HTTP 200; remove or update entries that redirect or error.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/sitemap-url-quality/',
            'training_link' => 'https://wpshadow.com/training/sitemap-optimization/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
