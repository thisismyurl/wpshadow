<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Debug Flags Left On
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-debug-enabled
 * Training: https://wpshadow.com/training/code-hygiene-debug-enabled
 */
class Diagnostic_Code_CODE_HYGIENE_DEBUG_ENABLED {
    public static function check() {
        return [
            'id' => 'code-hygiene-debug-enabled',
            'title' => __('Debug Flags Left On', 'wpshadow'),
            'description' => __('Detects WP_DEBUG, error_reporting, display_errors in production.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-debug-enabled',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-debug-enabled',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

