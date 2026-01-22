<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Radio Button Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-radio-button-consistency
 * Training: https://wpshadow.com/training/design-radio-button-consistency
 */
class Diagnostic_Design_RADIO_BUTTON_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-radio-button-consistency',
            'title' => __('Radio Button Consistency', 'wpshadow'),
            'description' => __('Validates radio buttons 18x18px minimum.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-radio-button-consistency',
            'training_link' => 'https://wpshadow.com/training/design-radio-button-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
