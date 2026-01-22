<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Data Retention Policy
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-retention-missing
 * Training: https://wpshadow.com/training/code-db-retention-missing
 */
class Diagnostic_Code_CODE_DB_RETENTION_MISSING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-retention-missing',
            'title' => __('Missing Data Retention Policy', 'wpshadow'),
            'description' => __('Flags tables with unbounded historical/log data.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-retention-missing',
            'training_link' => 'https://wpshadow.com/training/code-db-retention-missing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
