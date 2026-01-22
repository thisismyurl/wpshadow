<?php declare(strict_types=1);
/**
 * Form Validation UX Diagnostic
 *
 * Philosophy: Inline validation prevents errors
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Form_Validation_UX {
    public static function check() {
        return [
            'id' => 'seo-form-validation-ux',
            'title' => 'Form Validation User Experience',
            'description' => 'Implement inline validation, clear field requirements, helpful error messages.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/form-validation/',
            'training_link' => 'https://wpshadow.com/training/form-ux/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
