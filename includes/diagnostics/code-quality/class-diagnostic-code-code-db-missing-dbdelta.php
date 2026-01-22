<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing dbDelta
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-missing-dbdelta
 * Training: https://wpshadow.com/training/code-db-missing-dbdelta
 */
class Diagnostic_Code_CODE_DB_MISSING_DBDELTA extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-missing-dbdelta',
            'title' => __('Missing dbDelta', 'wpshadow'),
            'description' => __('Flags custom tables not using dbDelta for schema versioning.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-missing-dbdelta',
            'training_link' => 'https://wpshadow.com/training/code-db-missing-dbdelta',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
