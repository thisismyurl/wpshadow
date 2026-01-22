<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unsafe Serialized Data
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-serialized-unsafe
 * Training: https://wpshadow.com/training/code-db-serialized-unsafe
 */
class Diagnostic_Code_CODE_DB_SERIALIZED_UNSAFE {
    public static function check() {
        return [
            'id' => 'code-db-serialized-unsafe',
            'title' => __('Unsafe Serialized Data', 'wpshadow'),
            'description' => __('Detects unserialized data without validation or type checking.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-serialized-unsafe',
            'training_link' => 'https://wpshadow.com/training/code-db-serialized-unsafe',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

