<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Transient Churn Rate
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-transient-churn
 * Training: https://wpshadow.com/training/code-kpi-transient-churn
 */
class Diagnostic_Code_CODE_KPI_TRANSIENT_CHURN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-kpi-transient-churn',
            'title' => __('Transient Churn Rate', 'wpshadow'),
            'description' => __('Measures set/delete transient frequency (cache thrashing).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-transient-churn',
            'training_link' => 'https://wpshadow.com/training/code-kpi-transient-churn',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
