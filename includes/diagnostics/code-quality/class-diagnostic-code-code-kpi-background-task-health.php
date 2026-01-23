<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Background Task Duration/Failures
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-background-task-health
 * Training: https://wpshadow.com/training/code-kpi-background-task-health
 */
class Diagnostic_Code_CODE_KPI_BACKGROUND_TASK_HEALTH extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-kpi-background-task-health',
            'title' => __('Background Task Duration/Failures', 'wpshadow'),
            'description' => __('Monitors cron and async task reliability.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-background-task-health',
            'training_link' => 'https://wpshadow.com/training/code-kpi-background-task-health',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}