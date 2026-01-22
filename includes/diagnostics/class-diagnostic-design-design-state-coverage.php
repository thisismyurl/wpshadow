<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: State Coverage Audit
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-state-coverage
 * Training: https://wpshadow.com/training/design-state-coverage
 */
class Diagnostic_Design_DESIGN_STATE_COVERAGE {
    public static function check() {
        return [
            'id' => 'design-state-coverage',
            'title' => __('State Coverage Audit', 'wpshadow'),
            'description' => __('Verifies hover, focus, active, and disabled states across components.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-state-coverage',
            'training_link' => 'https://wpshadow.com/training/design-state-coverage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

