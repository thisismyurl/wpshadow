<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Excessive Cron Frequency
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-cron-interval
 * Training: https://wpshadow.com/training/code-perf-cron-interval
 */
class Diagnostic_Code_CODE_PERF_CRON_INTERVAL {
    public static function check() {
        return [
            'id' => 'code-perf-cron-interval',
            'title' => __('Excessive Cron Frequency', 'wpshadow'),
            'description' => __('Flags cron schedules with intervals < 1 minute overhead.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-cron-interval',
            'training_link' => 'https://wpshadow.com/training/code-perf-cron-interval',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

