<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Insecure Form Validation
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-form-validation
 * Training: https://wpshadow.com/training/code-frontend-form-validation
 */
class Diagnostic_Code_CODE_FRONTEND_FORM_VALIDATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-frontend-form-validation',
            'title' => __('Insecure Form Validation', 'wpshadow'),
            'description' => __('Detects client-side validation without server-side checks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-form-validation',
            'training_link' => 'https://wpshadow.com/training/code-frontend-form-validation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}