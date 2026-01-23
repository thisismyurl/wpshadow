<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: High Cyclomatic Complexity
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-cyclomatic-complexity
 * Training: https://wpshadow.com/training/code-standards-cyclomatic-complexity
 */
class Diagnostic_Code_CODE_STANDARDS_CYCLOMATIC_COMPLEXITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-cyclomatic-complexity',
            'title' => __('High Cyclomatic Complexity', 'wpshadow'),
            'description' => __('Detects functions exceeding complexity thresholds.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-cyclomatic-complexity',
            'training_link' => 'https://wpshadow.com/training/code-standards-cyclomatic-complexity',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}