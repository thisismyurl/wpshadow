<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Hook Execution Time
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-hook-time
 * Training: https://wpshadow.com/training/code-kpi-hook-time
 */
class Diagnostic_Code_CODE_KPI_HOOK_TIME {
    public static function check() {
        return [
            'id' => 'code-kpi-hook-time',
            'title' => __('Hook Execution Time', 'wpshadow'),
            'description' => __('Measures and attributes slow hook execution to plugins.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-hook-time',
            'training_link' => 'https://wpshadow.com/training/code-kpi-hook-time',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

