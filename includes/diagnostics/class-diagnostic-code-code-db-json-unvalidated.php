<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unvalidated JSON Storage
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-json-unvalidated
 * Training: https://wpshadow.com/training/code-db-json-unvalidated
 */
class Diagnostic_Code_CODE_DB_JSON_UNVALIDATED {
    public static function check() {
        return [
            'id' => 'code-db-json-unvalidated',
            'title' => __('Unvalidated JSON Storage', 'wpshadow'),
            'description' => __('Flags JSON stored without schema validation or casting.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-json-unvalidated',
            'training_link' => 'https://wpshadow.com/training/code-db-json-unvalidated',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

