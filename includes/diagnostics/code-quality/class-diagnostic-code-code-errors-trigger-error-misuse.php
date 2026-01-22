<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Misuse of trigger_error
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-trigger-error-misuse
 * Training: https://wpshadow.com/training/code-errors-trigger-error-misuse
 */
class Diagnostic_Code_CODE_ERRORS_TRIGGER_ERROR_MISUSE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-errors-trigger-error-misuse',
            'title' => __('Misuse of trigger_error', 'wpshadow'),
            'description' => __('Flags trigger_error with wrong severity levels.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-trigger-error-misuse',
            'training_link' => 'https://wpshadow.com/training/code-errors-trigger-error-misuse',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
