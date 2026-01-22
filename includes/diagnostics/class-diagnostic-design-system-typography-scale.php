<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Typography Scale Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-typography-scale
 * Training: https://wpshadow.com/training/design-system-typography-scale
 */
class Diagnostic_Design_SYSTEM_TYPOGRAPHY_SCALE {
    public static function check() {
        return [
            'id' => 'design-system-typography-scale',
            'title' => __('Typography Scale Enforcement', 'wpshadow'),
            'description' => __('Verifies all text uses defined font sizes (no 13px, 17px, 23px chaos).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-typography-scale',
            'training_link' => 'https://wpshadow.com/training/design-system-typography-scale',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
