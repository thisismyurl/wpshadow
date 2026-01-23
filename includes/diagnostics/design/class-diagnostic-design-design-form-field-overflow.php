<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Field Overflow
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-form-field-overflow
 * Training: https://wpshadow.com/training/design-form-field-overflow
 */
class Diagnostic_Design_DESIGN_FORM_FIELD_OVERFLOW extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-field-overflow',
            'title' => __('Form Field Overflow', 'wpshadow'),
            'description' => __('Checks inputs and textareas under expansion for overflow.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-field-overflow',
            'training_link' => 'https://wpshadow.com/training/design-form-field-overflow',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}