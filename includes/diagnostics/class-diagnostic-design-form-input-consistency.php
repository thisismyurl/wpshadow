<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Form Input Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-form-input-consistency
 * Training: https://wpshadow.com/training/design-form-input-consistency
 */
class Diagnostic_Design_FORM_INPUT_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-form-input-consistency',
            'title' => __('Form Input Consistency', 'wpshadow'),
            'description' => __('Verifies input fields, textareas, selects share consistent height (36-48px), padding.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-input-consistency',
            'training_link' => 'https://wpshadow.com/training/design-form-input-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
