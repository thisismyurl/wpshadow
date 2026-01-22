<?php declare(strict_types=1);
/**
 * TTFB Performance Diagnostic
 *
 * Philosophy: Fast server response time foundation
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_TTFB_Performance {
    public static function check() {
        return [
            'id' => 'seo-ttfb-performance',
            'title' => 'Time To First Byte (TTFB)',
            'description' => 'Monitor TTFB under 200ms. Slow TTFB indicates server, database, or caching issues. Use field data from Search Console.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/ttfb-optimization/',
            'training_link' => 'https://wpshadow.com/training/server-performance/',
            'auto_fixable' => false,
            'threat_level' => 65,
        ];
    }
}
