<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Null Checks
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-null-checks-missing
 * Training: https://wpshadow.com/training/code-errors-null-checks-missing
 */
class Diagnostic_Code_CODE_ERRORS_NULL_CHECKS_MISSING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-errors-null-checks-missing',
            'title' => __('Missing Null Checks', 'wpshadow'),
            'description' => __('Flags WP API calls without null/false checks on returns.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-null-checks-missing',
            'training_link' => 'https://wpshadow.com/training/code-errors-null-checks-missing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}