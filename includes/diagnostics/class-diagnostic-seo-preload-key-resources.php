<?php declare(strict_types=1);
/**
 * Preload Key Resources Diagnostic
 *
 * Philosophy: Preload fonts/images to speed rendering
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Preload_Key_Resources {
    public static function check() {
        return [
            'id' => 'seo-preload-key-resources',
            'title' => 'Preload Key Resources',
            'description' => 'Consider preloading critical fonts and hero images to improve LCP.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/preload-resources/',
            'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
