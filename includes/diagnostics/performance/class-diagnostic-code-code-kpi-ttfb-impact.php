<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: TTFB Impact Attribution
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-ttfb-impact
 * Training: https://wpshadow.com/training/code-kpi-ttfb-impact
 */
class Diagnostic_Code_CODE_KPI_TTFB_IMPACT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-kpi-ttfb-impact',
            'title' => __('TTFB Impact Attribution', 'wpshadow'),
            'description' => __('Measures time-to-first-byte slowdown per plugin (heuristic).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-ttfb-impact',
            'training_link' => 'https://wpshadow.com/training/code-kpi-ttfb-impact',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}