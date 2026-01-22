<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unused Selector Removal
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-unused-selectors
 * Training: https://wpshadow.com/training/design-debt-unused-selectors
 */
class Diagnostic_Design_DEBT_UNUSED_SELECTORS {
    public static function check() {
        return [
            'id' => 'design-debt-unused-selectors',
            'title' => __('Unused Selector Removal', 'wpshadow'),
            'description' => __('Recommends PurgeCSS improvements.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-unused-selectors',
            'training_link' => 'https://wpshadow.com/training/design-debt-unused-selectors',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
