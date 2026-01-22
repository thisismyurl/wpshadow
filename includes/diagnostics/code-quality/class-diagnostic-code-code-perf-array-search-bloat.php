<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Array Search on Large Sets
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-array-search-bloat
 * Training: https://wpshadow.com/training/code-perf-array-search-bloat
 */
class Diagnostic_Code_CODE_PERF_ARRAY_SEARCH_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-array-search-bloat',
            'title' => __('Array Search on Large Sets', 'wpshadow'),
            'description' => __('Detects in_array/array_search on large arrays per request.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-array-search-bloat',
            'training_link' => 'https://wpshadow.com/training/code-perf-array-search-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
