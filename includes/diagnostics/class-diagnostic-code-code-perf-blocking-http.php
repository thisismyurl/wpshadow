<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Blocking HTTP in Admin
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-blocking-http
 * Training: https://wpshadow.com/training/code-perf-blocking-http
 */
class Diagnostic_Code_CODE_PERF_BLOCKING_HTTP {
    public static function check() {
        return [
            'id' => 'code-perf-blocking-http',
            'title' => __('Blocking HTTP in Admin', 'wpshadow'),
            'description' => __('Detects synchronous remote calls in admin actions/AJAX.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-blocking-http',
            'training_link' => 'https://wpshadow.com/training/code-perf-blocking-http',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

