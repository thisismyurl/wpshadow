<?php declare(strict_types=1);
/**
 * Print Stylesheet SEO Diagnostic
 *
 * Philosophy: Print views should be clean
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Print_Stylesheet_SEO {
    public static function check() {
        return [
            'id' => 'seo-print-stylesheet-seo',
            'title' => 'Print Stylesheet Configuration',
            'description' => 'Provide print-optimized stylesheet for better user experience when printing pages.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/print-stylesheets/',
            'training_link' => 'https://wpshadow.com/training/print-optimization/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }
}
