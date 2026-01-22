<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Field Labeling Accessibility
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-form-field-labeling
 * Training: https://wpshadow.com/training/design-form-field-labeling
 */
class Diagnostic_Design_FORM_FIELD_LABELING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-field-labeling',
            'title' => __('Form Field Labeling Accessibility', 'wpshadow'),
            'description' => __('Verifies all form inputs have proper <label> elements.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-field-labeling',
            'training_link' => 'https://wpshadow.com/training/design-form-field-labeling',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
