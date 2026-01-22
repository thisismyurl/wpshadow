<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Transition Timing Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-transition-consistency
 * Training: https://wpshadow.com/training/design-system-transition-consistency
 */
class Diagnostic_Design_SYSTEM_TRANSITION_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-system-transition-consistency',
            'title' => __('Transition Timing Enforcement', 'wpshadow'),
            'description' => __('Verifies all transitions use defined timing functions (ease-out, linear, etc).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-transition-consistency',
            'training_link' => 'https://wpshadow.com/training/design-system-transition-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
