<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Orphaned CSS Detection
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-orphaned-styles
 * Training: https://wpshadow.com/training/design-debt-orphaned-styles
 */
class Diagnostic_Design_DEBT_ORPHANED_STYLES {
    public static function check() {
        return [
            'id' => 'design-debt-orphaned-styles',
            'title' => __('Orphaned CSS Detection', 'wpshadow'),
            'description' => __('Finds unused CSS selectors, dead style rules.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-orphaned-styles',
            'training_link' => 'https://wpshadow.com/training/design-debt-orphaned-styles',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
