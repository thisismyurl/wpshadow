<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Success Input Border Color
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-success-input-border-color
 * Training: https://wpshadow.com/training/design-success-input-border-color
 */
class Diagnostic_Design_SUCCESS_INPUT_BORDER_COLOR {
    public static function check() {
        return [
            'id' => 'design-success-input-border-color',
            'title' => __('Success Input Border Color', 'wpshadow'),
            'description' => __('Confirms success inputs have green border.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-success-input-border-color',
            'training_link' => 'https://wpshadow.com/training/design-success-input-border-color',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
