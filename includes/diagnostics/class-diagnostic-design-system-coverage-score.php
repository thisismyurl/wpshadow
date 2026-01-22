<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Design System Coverage Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-coverage-score
 * Training: https://wpshadow.com/training/design-system-coverage-score
 */
class Diagnostic_Design_SYSTEM_COVERAGE_SCORE {
    public static function check() {
        return [
            'id' => 'design-system-coverage-score',
            'title' => __('Design System Coverage Score', 'wpshadow'),
            'description' => __('Calculates % of design tokens actually used in code.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-coverage-score',
            'training_link' => 'https://wpshadow.com/training/design-system-coverage-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
