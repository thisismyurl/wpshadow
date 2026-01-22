<?php declare(strict_types=1);
/**
 * Video Sitemap Metadata Diagnostic
 *
 * Philosophy: Provide complete metadata for videos
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Video_Sitemap_Metadata {
    public static function check() {
        return [
            'id' => 'seo-video-sitemap-metadata',
            'title' => 'Video Sitemap Metadata Completeness',
            'description' => 'Ensure video sitemaps include thumbnail, duration, and player details when applicable.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/video-sitemap-metadata/',
            'training_link' => 'https://wpshadow.com/training/video-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
