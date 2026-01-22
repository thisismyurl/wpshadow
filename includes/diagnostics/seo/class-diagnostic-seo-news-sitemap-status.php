<?php
declare(strict_types=1);
/**
 * News Sitemap Status Diagnostic
 *
 * Philosophy: Publishers should follow news sitemap standards
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_News_Sitemap_Status extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-news-sitemap-status',
            'title' => 'News Sitemap Status',
            'description' => 'If a publisher, validate news sitemap format and timeliness for inclusion in Google News.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/news-sitemap/',
            'training_link' => 'https://wpshadow.com/training/news-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
