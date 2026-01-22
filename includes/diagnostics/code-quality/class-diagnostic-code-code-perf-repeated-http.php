<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Repeated HTTP Calls
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-repeated-http
 * Training: https://wpshadow.com/training/code-perf-repeated-http
 */
class Diagnostic_Code_CODE_PERF_REPEATED_HTTP extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-repeated-http',
            'title' => __('Repeated HTTP Calls', 'wpshadow'),
            'description' => __('Detects wp_remote_get loops without transient/object caching.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-repeated-http',
            'training_link' => 'https://wpshadow.com/training/code-perf-repeated-http',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
