<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Sensitive Data in Logs
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-sensitive-logging
 * Training: https://wpshadow.com/training/code-errors-sensitive-logging
 */
class Diagnostic_Code_CODE_ERRORS_SENSITIVE_LOGGING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-errors-sensitive-logging',
            'title' => __('Sensitive Data in Logs', 'wpshadow'),
            'description' => __('Flags tokens, passwords, or PII in error logs.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-sensitive-logging',
            'training_link' => 'https://wpshadow.com/training/code-errors-sensitive-logging',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}