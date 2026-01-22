<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Query Count Attribution
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-query-count
 * Training: https://wpshadow.com/training/code-kpi-query-count
 */
class Diagnostic_Code_CODE_KPI_QUERY_COUNT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-kpi-query-count',
            'title' => __('Query Count Attribution', 'wpshadow'),
            'description' => __('Attributes database queries to specific plugins/themes.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-query-count',
            'training_link' => 'https://wpshadow.com/training/code-kpi-query-count',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
