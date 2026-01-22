<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Uncached get_option
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-uncached-get-option
 * Training: https://wpshadow.com/training/code-perf-uncached-get-option
 */
class Diagnostic_Code_CODE_PERF_UNCACHED_GET_OPTION {
    public static function check() {
        return [
            'id' => 'code-perf-uncached-get-option',
            'title' => __('Uncached get_option', 'wpshadow'),
            'description' => __('Flags get_option in hot paths without caching or batching.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-uncached-get-option',
            'training_link' => 'https://wpshadow.com/training/code-perf-uncached-get-option',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

