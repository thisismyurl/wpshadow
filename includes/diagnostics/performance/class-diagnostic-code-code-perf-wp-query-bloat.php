<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WP_Query Without Limits
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-wp-query-bloat
 * Training: https://wpshadow.com/training/code-perf-wp-query-bloat
 */
class Diagnostic_Code_CODE_PERF_WP_QUERY_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-wp-query-bloat',
            'title' => __('WP_Query Without Limits', 'wpshadow'),
            'description' => __('Detects posts_per_page=-1 or unbounded queries on front-end.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-wp-query-bloat',
            'training_link' => 'https://wpshadow.com/training/code-perf-wp-query-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
