<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Import Redundancy
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-import-redundancy
 * Training: https://wpshadow.com/training/design-debt-import-redundancy
 */
class Diagnostic_Design_DEBT_IMPORT_REDUNDANCY {
    public static function check() {
        return [
            'id' => 'design-debt-import-redundancy',
            'title' => __('Import Redundancy', 'wpshadow'),
            'description' => __('Finds duplicate font/icon imports across stylesheets.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-import-redundancy',
            'training_link' => 'https://wpshadow.com/training/design-debt-import-redundancy',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
