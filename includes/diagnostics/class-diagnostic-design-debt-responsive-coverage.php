<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Responsive Coverage Ratio
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-responsive-coverage
 * Training: https://wpshadow.com/training/design-debt-responsive-coverage
 */
class Diagnostic_Design_DEBT_RESPONSIVE_COVERAGE {
    public static function check() {
        return [
            'id' => 'design-debt-responsive-coverage',
            'title' => __('Responsive Coverage Ratio', 'wpshadow'),
            'description' => __('% of components tested at all breakpoints.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-responsive-coverage',
            'training_link' => 'https://wpshadow.com/training/design-debt-responsive-coverage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
