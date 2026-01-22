<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: JS Not Minified
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-js-unminified
 * Training: https://wpshadow.com/training/code-memory-js-unminified
 */
class Diagnostic_Code_CODE_MEMORY_JS_UNMINIFIED {
    public static function check() {
        return [
            'id' => 'code-memory-js-unminified',
            'title' => __('JS Not Minified', 'wpshadow'),
            'description' => __('Flags non-minified JavaScript in production environments.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-js-unminified',
            'training_link' => 'https://wpshadow.com/training/code-memory-js-unminified',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

