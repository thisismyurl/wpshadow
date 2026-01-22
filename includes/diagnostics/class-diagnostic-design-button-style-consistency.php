<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Button Style Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-button-style-consistency
 * Training: https://wpshadow.com/training/design-button-style-consistency
 */
class Diagnostic_Design_BUTTON_STYLE_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-button-style-consistency',
            'title' => __('Button Style Consistency', 'wpshadow'),
            'description' => __('Verifies primary, secondary, tertiary buttons follow consistent sizing, padding, hover.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-style-consistency',
            'training_link' => 'https://wpshadow.com/training/design-button-style-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
