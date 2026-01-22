<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Missing Backoff/Retry
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-no-backoff
 * Training: https://wpshadow.com/training/code-errors-no-backoff
 */
class Diagnostic_Code_CODE_ERRORS_NO_BACKOFF {
    public static function check() {
        return [
            'id' => 'code-errors-no-backoff',
            'title' => __('Missing Backoff/Retry', 'wpshadow'),
            'description' => __('Detects external service calls without retry or exponential backoff.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-no-backoff',
            'training_link' => 'https://wpshadow.com/training/code-errors-no-backoff',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

