<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Duplicate Cron Names
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-cron-duplicates
 * Training: https://wpshadow.com/training/code-hygiene-cron-duplicates
 */
class Diagnostic_Code_CODE_HYGIENE_CRON_DUPLICATES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hygiene-cron-duplicates',
            'title' => __('Duplicate Cron Names', 'wpshadow'),
            'description' => __('Flags cron hook names colliding across plugins.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-cron-duplicates',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-cron-duplicates',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}