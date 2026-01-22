<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Theme Color Metadata
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-theme-color-metadata
 * Training: https://wpshadow.com/training/design-theme-color-metadata
 */
class Diagnostic_Design_THEME_COLOR_METADATA {
    public static function check() {
        return [
            'id' => 'design-theme-color-metadata',
            'title' => __('Theme Color Metadata', 'wpshadow'),
            'description' => __('Validates theme-color meta tag set.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-theme-color-metadata',
            'training_link' => 'https://wpshadow.com/training/design-theme-color-metadata',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
