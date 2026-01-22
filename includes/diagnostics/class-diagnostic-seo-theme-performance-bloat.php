<?php declare(strict_types=1);
/**
 * Theme Performance Bloat Diagnostic
 *
 * Philosophy: Lean themes perform better
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Theme_Performance_Bloat {
    public static function check() {
        return [
            'id' => 'seo-theme-performance-bloat',
            'title' => 'Theme Performance Bloat',
            'description' => 'Review theme for unused features, excessive scripts, and performance overhead. Consider lightweight alternatives.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/theme-performance/',
            'training_link' => 'https://wpshadow.com/training/theme-selection/',
            'auto_fixable' => false,
            'threat_level' => 45,
        ];
    }
}
