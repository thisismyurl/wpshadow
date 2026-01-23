<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Duplicate Cron Events
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-single-event-dupes
 * Training: https://wpshadow.com/training/code-perf-single-event-dupes
 */
class Diagnostic_Code_CODE_PERF_SINGLE_EVENT_DUPES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-single-event-dupes',
            'title' => __('Duplicate Cron Events', 'wpshadow'),
            'description' => __('Detects wp_schedule_single_event creating duplicate events.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-single-event-dupes',
            'training_link' => 'https://wpshadow.com/training/code-perf-single-event-dupes',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}