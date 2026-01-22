<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: apply_filters in Tight Loops
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-filter-loop-abuse
 * Training: https://wpshadow.com/training/code-perf-filter-loop-abuse
 */
class Diagnostic_Code_CODE_PERF_FILTER_LOOP_ABUSE {
    public static function check() {
        return [
            'id' => 'code-perf-filter-loop-abuse',
            'title' => __('apply_filters in Tight Loops', 'wpshadow'),
            'description' => __('Detects apply_filters called per iteration instead of once.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-filter-loop-abuse',
            'training_link' => 'https://wpshadow.com/training/code-perf-filter-loop-abuse',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

