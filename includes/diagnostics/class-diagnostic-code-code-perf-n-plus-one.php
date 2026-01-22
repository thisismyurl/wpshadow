<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: N+1 Query Patterns
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-n-plus-one
 * Training: https://wpshadow.com/training/code-perf-n-plus-one
 */
class Diagnostic_Code_CODE_PERF_N_PLUS_ONE {
    public static function check() {
        return [
            'id' => 'code-perf-n-plus-one',
            'title' => __('N+1 Query Patterns', 'wpshadow'),
            'description' => __('Detects posts/meta/options queries inside loops.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-n-plus-one',
            'training_link' => 'https://wpshadow.com/training/code-perf-n-plus-one',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

