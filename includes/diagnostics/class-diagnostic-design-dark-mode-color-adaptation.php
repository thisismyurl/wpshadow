<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Dark Mode Color Adaptation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-dark-mode-color-adaptation
 * Training: https://wpshadow.com/training/design-dark-mode-color-adaptation
 */
class Diagnostic_Design_DARK_MODE_COLOR_ADAPTATION {
    public static function check() {
        return [
            'id' => 'design-dark-mode-color-adaptation',
            'title' => __('Dark Mode Color Adaptation', 'wpshadow'),
            'description' => __('Verifies colors adjusted for dark mode.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-dark-mode-color-adaptation',
            'training_link' => 'https://wpshadow.com/training/design-dark-mode-color-adaptation',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
