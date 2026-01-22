<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: CSS Custom Properties Usage
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-custom-properties
 * Training: https://wpshadow.com/training/design-css-custom-properties
 */
class Diagnostic_Design_CSS_CUSTOM_PROPERTIES {
    public static function check() {
        return [
            'id' => 'design-css-custom-properties',
            'title' => __('CSS Custom Properties Usage', 'wpshadow'),
            'description' => __('Checks CSS custom properties used.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-custom-properties',
            'training_link' => 'https://wpshadow.com/training/design-css-custom-properties',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
