<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Memory Per Request
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-memory-usage
 * Training: https://wpshadow.com/training/code-kpi-memory-usage
 */
class Diagnostic_Code_CODE_KPI_MEMORY_USAGE {
    public static function check() {
        return [
            'id' => 'code-kpi-memory-usage',
            'title' => __('Memory Per Request', 'wpshadow'),
            'description' => __('Attributes peak memory consumption to plugins/themes.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-memory-usage',
            'training_link' => 'https://wpshadow.com/training/code-kpi-memory-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

