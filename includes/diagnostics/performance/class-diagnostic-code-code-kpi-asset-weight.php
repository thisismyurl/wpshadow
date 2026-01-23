<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Asset Weight Attribution
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-asset-weight
 * Training: https://wpshadow.com/training/code-kpi-asset-weight
 */
class Diagnostic_Code_CODE_KPI_ASSET_WEIGHT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-kpi-asset-weight',
            'title' => __('Asset Weight Attribution', 'wpshadow'),
            'description' => __('Sums CSS/JS bytes loaded per plugin/theme (transfer cost).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-asset-weight',
            'training_link' => 'https://wpshadow.com/training/code-kpi-asset-weight',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}