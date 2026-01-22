<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: JavaScript Bundle Size
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-javascript-bundle-size
 * Training: https://wpshadow.com/training/design-javascript-bundle-size
 */
class Diagnostic_Design_JAVASCRIPT_BUNDLE_SIZE {
    public static function check() {
        return [
            'id' => 'design-javascript-bundle-size',
            'title' => __('JavaScript Bundle Size', 'wpshadow'),
            'description' => __('Confirms JS bundle under 200KB gzipped.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-javascript-bundle-size',
            'training_link' => 'https://wpshadow.com/training/design-javascript-bundle-size',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
