<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Object Cache Unused
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-no-object-cache
 * Training: https://wpshadow.com/training/code-perf-no-object-cache
 */
class Diagnostic_Code_CODE_PERF_NO_OBJECT_CACHE {
    public static function check() {
        return [
            'id' => 'code-perf-no-object-cache',
            'title' => __('Object Cache Unused', 'wpshadow'),
            'description' => __('Flags repeated computations where object caching present.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-no-object-cache',
            'training_link' => 'https://wpshadow.com/training/code-perf-no-object-cache',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

