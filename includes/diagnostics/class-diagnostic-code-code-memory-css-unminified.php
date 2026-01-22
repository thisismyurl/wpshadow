<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: CSS Not Minified
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-css-unminified
 * Training: https://wpshadow.com/training/code-memory-css-unminified
 */
class Diagnostic_Code_CODE_MEMORY_CSS_UNMINIFIED {
    public static function check() {
        return [
            'id' => 'code-memory-css-unminified',
            'title' => __('CSS Not Minified', 'wpshadow'),
            'description' => __('Flags non-minified CSS in production environments.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-css-unminified',
            'training_link' => 'https://wpshadow.com/training/code-memory-css-unminified',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

