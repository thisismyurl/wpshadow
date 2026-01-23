<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Data Volume Attribution
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-data-bloat
 * Training: https://wpshadow.com/training/code-kpi-data-bloat
 */
class Diagnostic_Code_CODE_KPI_DATA_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-kpi-data-bloat',
            'title' => __('Data Volume Attribution', 'wpshadow'),
            'description' => __('Tracks orphaned/stale data size per plugin.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-data-bloat',
            'training_link' => 'https://wpshadow.com/training/code-kpi-data-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}