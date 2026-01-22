<?php declare(strict_types=1);
/**
 * YouTube SEO Integration Diagnostic
 *
 * Philosophy: YouTube is second largest search engine
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_YouTube_SEO_Integration {
    public static function check() {
        return [
            'id' => 'seo-youtube-seo-integration',
            'title' => 'YouTube SEO Optimization',
            'description' => 'Optimize YouTube videos: keyword-rich titles, descriptions, tags, playlists, cards.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/youtube-seo/',
            'training_link' => 'https://wpshadow.com/training/youtube-optimization/',
            'auto_fixable' => false,
            'threat_level' => 30,
        ];
    }
}
