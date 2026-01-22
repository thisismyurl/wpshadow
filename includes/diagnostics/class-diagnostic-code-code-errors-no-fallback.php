<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Missing Feature Fallback
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-no-fallback
 * Training: https://wpshadow.com/training/code-errors-no-fallback
 */
class Diagnostic_Code_CODE_ERRORS_NO_FALLBACK {
    public static function check() {
        return [
            'id' => 'code-errors-no-fallback',
            'title' => __('Missing Feature Fallback', 'wpshadow'),
            'description' => __('Detects code assuming optional features always present.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-no-fallback',
            'training_link' => 'https://wpshadow.com/training/code-errors-no-fallback',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

