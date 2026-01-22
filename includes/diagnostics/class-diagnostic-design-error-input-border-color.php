<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Error Input Border Color
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-error-input-border-color
 * Training: https://wpshadow.com/training/design-error-input-border-color
 */
class Diagnostic_Design_ERROR_INPUT_BORDER_COLOR {
    public static function check() {
        return [
            'id' => 'design-error-input-border-color',
            'title' => __('Error Input Border Color', 'wpshadow'),
            'description' => __('Checks error inputs have red border.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-error-input-border-color',
            'training_link' => 'https://wpshadow.com/training/design-error-input-border-color',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
