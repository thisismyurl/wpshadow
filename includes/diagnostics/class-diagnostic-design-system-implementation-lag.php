<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Design System Implementation Lag
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-implementation-lag
 * Training: https://wpshadow.com/training/design-system-implementation-lag
 */
class Diagnostic_Design_SYSTEM_IMPLEMENTATION_LAG {
    public static function check() {
        return [
            'id' => 'design-system-implementation-lag',
            'title' => __('Design System Implementation Lag', 'wpshadow'),
            'description' => __('Detects components in design system but not implemented in code.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-implementation-lag',
            'training_link' => 'https://wpshadow.com/training/design-system-implementation-lag',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
