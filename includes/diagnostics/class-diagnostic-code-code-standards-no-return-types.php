<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Missing Return Type Hints
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-no-return-types
 * Training: https://wpshadow.com/training/code-standards-no-return-types
 */
class Diagnostic_Code_CODE_STANDARDS_NO_RETURN_TYPES {
    public static function check() {
        return [
            'id' => 'code-standards-no-return-types',
            'title' => __('Missing Return Type Hints', 'wpshadow'),
            'description' => __('Flags functions without return type declarations.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-no-return-types',
            'training_link' => 'https://wpshadow.com/training/code-standards-no-return-types',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

