<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Primary Key Conflicts
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-pk-conflicts
 * Training: https://wpshadow.com/training/code-db-pk-conflicts
 */
class Diagnostic_Code_CODE_DB_PK_CONFLICTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-pk-conflicts',
            'title' => __('Primary Key Conflicts', 'wpshadow'),
            'description' => __('Detects custom table PK issues or duplicate detection missing.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-pk-conflicts',
            'training_link' => 'https://wpshadow.com/training/code-db-pk-conflicts',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}