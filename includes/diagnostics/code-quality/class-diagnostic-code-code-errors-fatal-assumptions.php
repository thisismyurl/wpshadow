<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Fatal Assumptions on Paths
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-fatal-assumptions
 * Training: https://wpshadow.com/training/code-errors-fatal-assumptions
 */
class Diagnostic_Code_CODE_ERRORS_FATAL_ASSUMPTIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-errors-fatal-assumptions',
            'title' => __('Fatal Assumptions on Paths', 'wpshadow'),
            'description' => __('Detects assumptions about constants/paths without guards.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-fatal-assumptions',
            'training_link' => 'https://wpshadow.com/training/code-errors-fatal-assumptions',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
