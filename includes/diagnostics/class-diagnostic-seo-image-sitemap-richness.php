<?php declare(strict_types=1);
/**
 * Image Sitemap Richness Diagnostic
 *
 * Philosophy: Provide captions/geo/licensing where relevant
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Image_Sitemap_Richness {
    public static function check() {
        return [
            'id' => 'seo-image-sitemap-richness',
            'title' => 'Image Sitemap Richness',
            'description' => 'Enhance image sitemaps with captions, titles, and licensing info where applicable to improve media understanding.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/image-sitemap-richness/',
            'training_link' => 'https://wpshadow.com/training/sitemap-optimization/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
