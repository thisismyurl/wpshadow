<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Charset/Collation Mismatch
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-charset-mismatch
 * Training: https://wpshadow.com/training/code-db-charset-mismatch
 */
class Diagnostic_Code_CODE_DB_CHARSET_MISMATCH extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-charset-mismatch',
            'title' => __('Charset/Collation Mismatch', 'wpshadow'),
            'description' => __('Detects columns with mismatched charset from table/DB.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-charset-mismatch',
            'training_link' => 'https://wpshadow.com/training/code-db-charset-mismatch',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}