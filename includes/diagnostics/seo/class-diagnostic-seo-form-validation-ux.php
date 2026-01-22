<?php
declare(strict_types=1);
/**
 * Form Validation UX Diagnostic
 *
 * Philosophy: Inline validation prevents errors
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Form_Validation_UX extends Diagnostic_Base {
    public static function check(): ?array {
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
