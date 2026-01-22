<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Transient Churn
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-transient-churn
 * Training: https://wpshadow.com/training/code-perf-transient-churn
 */
class Diagnostic_Code_CODE_PERF_TRANSIENT_CHURN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-transient-churn',
            'title' => __('Transient Churn', 'wpshadow'),
            'description' => __('Detects excessive set_transient/delete_transient calls per request.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-transient-churn',
            'training_link' => 'https://wpshadow.com/training/code-perf-transient-churn',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
