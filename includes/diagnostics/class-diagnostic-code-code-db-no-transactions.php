<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Missing Transactions
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-no-transactions
 * Training: https://wpshadow.com/training/code-db-no-transactions
 */
class Diagnostic_Code_CODE_DB_NO_TRANSACTIONS {
    public static function check() {
        return [
            'id' => 'code-db-no-transactions',
            'title' => __('Missing Transactions', 'wpshadow'),
            'description' => __('Flags multi-step operations without transaction support.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-no-transactions',
            'training_link' => 'https://wpshadow.com/training/code-db-no-transactions',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

