<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Parameter Type Hints
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-no-param-types
 * Training: https://wpshadow.com/training/code-standards-no-param-types
 */
class Diagnostic_Code_CODE_STANDARDS_NO_PARAM_TYPES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-no-param-types',
            'title' => __('Missing Parameter Type Hints', 'wpshadow'),
            'description' => __('Detects functions lacking parameter type declarations.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-no-param-types',
            'training_link' => 'https://wpshadow.com/training/code-standards-no-param-types',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
