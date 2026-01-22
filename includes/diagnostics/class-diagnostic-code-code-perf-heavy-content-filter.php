<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Heavy Content Filters
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-heavy-content-filter
 * Training: https://wpshadow.com/training/code-perf-heavy-content-filter
 */
class Diagnostic_Code_CODE_PERF_HEAVY_CONTENT_FILTER {
    public static function check() {
        return [
            'id' => 'code-perf-heavy-content-filter',
            'title' => __('Heavy Content Filters', 'wpshadow'),
            'description' => __('Detects expensive operations in the_content/the_excerpt filters.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-heavy-content-filter',
            'training_link' => 'https://wpshadow.com/training/code-perf-heavy-content-filter',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

