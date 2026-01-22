<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Duplicate Style Declaration
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-duplicate-styles
 * Training: https://wpshadow.com/training/design-debt-duplicate-styles
 */
class Diagnostic_Design_DEBT_DUPLICATE_STYLES {
    public static function check() {
        return [
            'id' => 'design-debt-duplicate-styles',
            'title' => __('Duplicate Style Declaration', 'wpshadow'),
            'description' => __('Finds same properties defined multiple times.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-duplicate-styles',
            'training_link' => 'https://wpshadow.com/training/design-debt-duplicate-styles',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
