<?php declare(strict_types=1);
/**
 * Lazyload Thresholds Diagnostic
 *
 * Philosophy: Avoid over-aggressive lazyloading above the fold
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Lazyload_Thresholds {
    public static function check() {
        return [
            'id' => 'seo-lazyload-thresholds',
            'title' => 'Lazyload Thresholds',
            'description' => 'Ensure lazyload thresholds do not affect critical above-the-fold content and LCP images.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/lazyload-thresholds/',
            'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
