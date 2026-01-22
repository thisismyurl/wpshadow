<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Duplicate Code Blocks
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-duplicate-code
 * Training: https://wpshadow.com/training/code-standards-duplicate-code
 */
class Diagnostic_Code_CODE_STANDARDS_DUPLICATE_CODE {
    public static function check() {
        return [
            'id' => 'code-standards-duplicate-code',
            'title' => __('Duplicate Code Blocks', 'wpshadow'),
            'description' => __('Detects copy-paste code across plugins/themes (DRY violation).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-duplicate-code',
            'training_link' => 'https://wpshadow.com/training/code-standards-duplicate-code',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

