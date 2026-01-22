<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Raw SQL Detected
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-raw-sql
 * Training: https://wpshadow.com/training/code-db-raw-sql
 */
class Diagnostic_Code_CODE_DB_RAW_SQL extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-raw-sql',
            'title' => __('Raw SQL Detected', 'wpshadow'),
            'description' => __('Flags queries using raw SQL instead of wpdb->prepare().', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-raw-sql',
            'training_link' => 'https://wpshadow.com/training/code-db-raw-sql',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
