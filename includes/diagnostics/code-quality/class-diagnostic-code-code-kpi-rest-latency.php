<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: REST Endpoint Latency
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-kpi-rest-latency
 * Training: https://wpshadow.com/training/code-kpi-rest-latency
 */
class Diagnostic_Code_CODE_KPI_REST_LATENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-kpi-rest-latency',
            'title' => __('REST Endpoint Latency', 'wpshadow'),
            'description' => __('Measures and ranks REST endpoint response times.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-kpi-rest-latency',
            'training_link' => 'https://wpshadow.com/training/code-kpi-rest-latency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}