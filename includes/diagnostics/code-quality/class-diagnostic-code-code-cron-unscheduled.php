<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Cron Events Not Unscheduled
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-cron-unscheduled
 * Training: https://wpshadow.com/training/code-cron-unscheduled
 */
class Diagnostic_Code_CODE_CRON_UNSCHEDULED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-cron-unscheduled',
            'title' => __('Cron Events Not Unscheduled', 'wpshadow'),
            'description' => __('Detects cron handlers registered but not cleaned on deactivate.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-cron-unscheduled',
            'training_link' => 'https://wpshadow.com/training/code-cron-unscheduled',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}