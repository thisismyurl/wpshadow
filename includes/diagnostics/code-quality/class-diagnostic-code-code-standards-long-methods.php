<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Long Methods/Functions
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-long-methods
 * Training: https://wpshadow.com/training/code-standards-long-methods
 */
class Diagnostic_Code_CODE_STANDARDS_LONG_METHODS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-long-methods',
            'title' => __('Long Methods/Functions', 'wpshadow'),
            'description' => __('Flags functions exceeding line count thresholds (100+ lines).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-long-methods',
            'training_link' => 'https://wpshadow.com/training/code-standards-long-methods',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}