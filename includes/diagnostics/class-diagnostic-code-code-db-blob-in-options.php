<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: BLOBs Stored in Options
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-blob-in-options
 * Training: https://wpshadow.com/training/code-db-blob-in-options
 */
class Diagnostic_Code_CODE_DB_BLOB_IN_OPTIONS {
    public static function check() {
        return [
            'id' => 'code-db-blob-in-options',
            'title' => __('BLOBs Stored in Options', 'wpshadow'),
            'description' => __('Flags large binary data stored in wp_options (bad practice).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-blob-in-options',
            'training_link' => 'https://wpshadow.com/training/code-db-blob-in-options',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

