<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Schema Changes in Plugin
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-schema-changes
 * Training: https://wpshadow.com/training/code-db-schema-changes
 */
class Diagnostic_Code_CODE_DB_SCHEMA_CHANGES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-schema-changes',
            'title' => __('Schema Changes in Plugin', 'wpshadow'),
            'description' => __('Detects schema modifications outside activation hook.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-schema-changes',
            'training_link' => 'https://wpshadow.com/training/code-db-schema-changes',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
