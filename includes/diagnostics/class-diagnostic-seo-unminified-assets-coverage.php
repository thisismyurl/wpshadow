<?php declare(strict_types=1);
/**
 * Unminified Assets Coverage Diagnostic
 *
 * Philosophy: Use minified JS/CSS to reduce payloads
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Unminified_Assets_Coverage {
    public static function check() {
        return [
            'id' => 'seo-unminified-assets-coverage',
            'title' => 'Unminified Assets Coverage',
            'description' => 'Ensure production uses minified JS/CSS assets to reduce transfer size and improve performance.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/minified-assets/',
            'training_link' => 'https://wpshadow.com/training/performance-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
