<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unstructured Logging
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-no-structured-logs
 * Training: https://wpshadow.com/training/code-errors-no-structured-logs
 */
class Diagnostic_Code_CODE_ERRORS_NO_STRUCTURED_LOGS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-errors-no-structured-logs',
            'title' => __('Unstructured Logging', 'wpshadow'),
            'description' => __('Flags logging without context/metadata for analysis.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-no-structured-logs',
            'training_link' => 'https://wpshadow.com/training/code-errors-no-structured-logs',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
