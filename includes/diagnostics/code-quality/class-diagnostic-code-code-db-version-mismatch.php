<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Schema Version Mismatch
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-version-mismatch
 * Training: https://wpshadow.com/training/code-db-version-mismatch
 */
class Diagnostic_Code_CODE_DB_VERSION_MISMATCH extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-version-mismatch',
            'title' => __('Schema Version Mismatch', 'wpshadow'),
            'description' => __('Detects code assuming old schema without migration checks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-version-mismatch',
            'training_link' => 'https://wpshadow.com/training/code-db-version-mismatch',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}