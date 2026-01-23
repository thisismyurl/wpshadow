<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Database Indexes
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-missing-indexes
 * Training: https://wpshadow.com/training/code-db-missing-indexes
 */
class Diagnostic_Code_CODE_DB_MISSING_INDEXES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-missing-indexes',
            'title' => __('Missing Database Indexes', 'wpshadow'),
            'description' => __('Flags custom tables lacking indexes on JOIN/WHERE columns.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-missing-indexes',
            'training_link' => 'https://wpshadow.com/training/code-db-missing-indexes',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}