<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Button Color Distinction
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-button-color-distinction
 * Training: https://wpshadow.com/training/design-button-color-distinction
 */
class Diagnostic_Design_BUTTON_COLOR_DISTINCTION {
    public static function check() {
        return [
            'id' => 'design-button-color-distinction',
            'title' => __('Button Color Distinction', 'wpshadow'),
            'description' => __('Validates button types visually distinct.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-color-distinction',
            'training_link' => 'https://wpshadow.com/training/design-button-color-distinction',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
