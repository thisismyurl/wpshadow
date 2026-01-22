<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Verbose Logging in Production
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-verbose-production
 * Training: https://wpshadow.com/training/code-errors-verbose-production
 */
class Diagnostic_Code_CODE_ERRORS_VERBOSE_PRODUCTION {
    public static function check() {
        return [
            'id' => 'code-errors-verbose-production',
            'title' => __('Verbose Logging in Production', 'wpshadow'),
            'description' => __('Detects excessive logging that shouldn\'t reach production.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-verbose-production',
            'training_link' => 'https://wpshadow.com/training/code-errors-verbose-production',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

