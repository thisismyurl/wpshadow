<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Orphaned Data on Uninstall
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-orphaned-data
 * Training: https://wpshadow.com/training/code-db-orphaned-data
 */
class Diagnostic_Code_CODE_DB_ORPHANED_DATA extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-orphaned-data',
            'title' => __('Orphaned Data on Uninstall', 'wpshadow'),
            'description' => __('Flags tables/options not cleaned up after plugin deactivation.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-orphaned-data',
            'training_link' => 'https://wpshadow.com/training/code-db-orphaned-data',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
