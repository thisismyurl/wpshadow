<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Validation Feedback Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-form-validation-feedback
 * Training: https://wpshadow.com/training/design-form-validation-feedback
 */
class Diagnostic_Design_FORM_VALIDATION_FEEDBACK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-validation-feedback',
            'title' => __('Form Validation Feedback Design', 'wpshadow'),
            'description' => __('Verifies error states use color + icon + text (not just color), success clear.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-validation-feedback',
            'training_link' => 'https://wpshadow.com/training/design-form-validation-feedback',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
