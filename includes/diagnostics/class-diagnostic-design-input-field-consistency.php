<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Input Field Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-input-field-consistency
 * Training: https://wpshadow.com/training/design-input-field-consistency
 */
class Diagnostic_Design_INPUT_FIELD_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-input-field-consistency',
            'title' => __('Input Field Consistency', 'wpshadow'),
            'description' => __('Validates all inputs match styling.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-input-field-consistency',
            'training_link' => 'https://wpshadow.com/training/design-input-field-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
