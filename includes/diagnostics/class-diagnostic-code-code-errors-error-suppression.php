<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Error Suppression @
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-error-suppression
 * Training: https://wpshadow.com/training/code-errors-error-suppression
 */
class Diagnostic_Code_CODE_ERRORS_ERROR_SUPPRESSION {
    public static function check() {
        return [
            'id' => 'code-errors-error-suppression',
            'title' => __('Error Suppression @', 'wpshadow'),
            'description' => __('Detects @ operator suppressing errors instead of handling.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-error-suppression',
            'training_link' => 'https://wpshadow.com/training/code-errors-error-suppression',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

