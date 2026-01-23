<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Meta/Term Query Cost
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-meta-query-cost
 * Training: https://wpshadow.com/training/code-perf-meta-query-cost
 */
class Diagnostic_Code_CODE_PERF_META_QUERY_COST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-meta-query-cost',
            'title' => __('Meta/Term Query Cost', 'wpshadow'),
            'description' => __('Flags meta_query/tax_query without indexes or leading wildcards.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-meta-query-cost',
            'training_link' => 'https://wpshadow.com/training/code-perf-meta-query-cost',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}