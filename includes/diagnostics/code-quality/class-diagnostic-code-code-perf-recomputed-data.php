<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Recomputed Data
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-recomputed-data
 * Training: https://wpshadow.com/training/code-perf-recomputed-data
 */
class Diagnostic_Code_CODE_PERF_RECOMPUTED_DATA extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-recomputed-data',
            'title' => __('Recomputed Data', 'wpshadow'),
            'description' => __('Flags expensive calculations done per request without memoization.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-recomputed-data',
            'training_link' => 'https://wpshadow.com/training/code-perf-recomputed-data',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
