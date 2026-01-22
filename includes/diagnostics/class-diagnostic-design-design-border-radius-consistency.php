<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Border Radius Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-border-radius-consistency
 * Training: https://wpshadow.com/training/design-border-radius-consistency
 */
class Diagnostic_Design_DESIGN_BORDER_RADIUS_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-border-radius-consistency',
            'title' => __('Border Radius Consistency', 'wpshadow'),
            'description' => __('Detects mixed radii within the same component type.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-border-radius-consistency',
            'training_link' => 'https://wpshadow.com/training/design-border-radius-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

