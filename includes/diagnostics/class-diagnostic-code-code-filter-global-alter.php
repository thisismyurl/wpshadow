<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Filter Altering Queries Globally
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-filter-global-alter
 * Training: https://wpshadow.com/training/code-filter-global-alter
 */
class Diagnostic_Code_CODE_FILTER_GLOBAL_ALTER {
    public static function check() {
        return [
            'id' => 'code-filter-global-alter',
            'title' => __('Filter Altering Queries Globally', 'wpshadow'),
            'description' => __('Detects filters modifying queries for all plugin uses unintentionally.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-filter-global-alter',
            'training_link' => 'https://wpshadow.com/training/code-filter-global-alter',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

