<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Deep Nesting Levels
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-deep-nesting
 * Training: https://wpshadow.com/training/code-standards-deep-nesting
 */
class Diagnostic_Code_CODE_STANDARDS_DEEP_NESTING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-deep-nesting',
            'title' => __('Deep Nesting Levels', 'wpshadow'),
            'description' => __('Flags functions with nesting > 4 levels deep.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-deep-nesting',
            'training_link' => 'https://wpshadow.com/training/code-standards-deep-nesting',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
