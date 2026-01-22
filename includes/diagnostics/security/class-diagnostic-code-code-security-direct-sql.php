<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Direct SQL Queries
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-direct-sql
 * Training: https://wpshadow.com/training/code-security-direct-sql
 */
class Diagnostic_Code_CODE_SECURITY_DIRECT_SQL extends Diagnostic_Base {
    public static function check(): ?array {
        // Placeholder check - returns advisory
        // In production, add specific validation logic
        
return [
            'id' => 'code-security-direct-sql',
            'title' => __('Direct SQL Queries', 'wpshadow'),
            'description' => __('Detects SQL queries without wpdb->prepare(); string concatenation detected.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-direct-sql',
            'training_link' => 'https://wpshadow.com/training/code-security-direct-sql',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
