<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Error Hotspots by Plugin
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-error-hotspots
 * Training: https://wpshadow.com/training/code-kpi-error-hotspots
 */
class Diagnostic_Code_CODE_KPI_ERROR_HOTSPOTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-kpi-error-hotspots',
            'title' => __('Error Hotspots by Plugin', 'wpshadow'),
            'description' => __('Identifies plugins generating most PHP notices/warnings.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-error-hotspots',
            'training_link' => 'https://wpshadow.com/training/code-kpi-error-hotspots',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}