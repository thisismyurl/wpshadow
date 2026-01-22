<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Browser Compatibility Testing
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-browser-compatibility-testing
 * Training: https://wpshadow.com/training/design-browser-compatibility-testing
 */
class Diagnostic_Design_BROWSER_COMPATIBILITY_TESTING {
    public static function check() {
        return [
            'id' => 'design-browser-compatibility-testing',
            'title' => __('Browser Compatibility Testing', 'wpshadow'),
            'description' => __('Validates site works in modern browsers.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-browser-compatibility-testing',
            'training_link' => 'https://wpshadow.com/training/design-browser-compatibility-testing',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
