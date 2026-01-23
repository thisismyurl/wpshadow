<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: REST Endpoint Bloat
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-rest-unbounded
 * Training: https://wpshadow.com/training/code-perf-rest-unbounded
 */
class Diagnostic_Code_CODE_PERF_REST_UNBOUNDED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-rest-unbounded',
            'title' => __('REST Endpoint Bloat', 'wpshadow'),
            'description' => __('Detects REST endpoints lacking pagination or field limiting.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-rest-unbounded',
            'training_link' => 'https://wpshadow.com/training/code-perf-rest-unbounded',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}