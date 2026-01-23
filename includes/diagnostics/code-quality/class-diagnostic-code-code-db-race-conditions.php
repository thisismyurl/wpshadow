<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Race Conditions on Updates
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-db-race-conditions
 * Training: https://wpshadow.com/training/code-db-race-conditions
 */
class Diagnostic_Code_CODE_DB_RACE_CONDITIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-db-race-conditions',
            'title' => __('Race Conditions on Updates', 'wpshadow'),
            'description' => __('Detects get_option/update_option patterns prone to races.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-db-race-conditions',
            'training_link' => 'https://wpshadow.com/training/code-db-race-conditions',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}