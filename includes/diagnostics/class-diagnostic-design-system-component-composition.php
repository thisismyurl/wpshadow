<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Component Composition Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-component-composition
 * Training: https://wpshadow.com/training/design-system-component-composition
 */
class Diagnostic_Design_SYSTEM_COMPONENT_COMPOSITION {
    public static function check() {
        return [
            'id' => 'design-system-component-composition',
            'title' => __('Component Composition Enforcement', 'wpshadow'),
            'description' => __('Detects improper component composition (missing hierarchy).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-component-composition',
            'training_link' => 'https://wpshadow.com/training/design-system-component-composition',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
