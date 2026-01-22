<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Spacing Violation Detection
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-spacing-violations
 * Training: https://wpshadow.com/training/design-system-spacing-violations
 */
class Diagnostic_Design_SYSTEM_SPACING_VIOLATIONS {
    public static function check() {
        return [
            'id' => 'design-system-spacing-violations',
            'title' => __('Spacing Violation Detection', 'wpshadow'),
            'description' => __('Detects margin/padding not on grid scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-spacing-violations',
            'training_link' => 'https://wpshadow.com/training/design-system-spacing-violations',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
