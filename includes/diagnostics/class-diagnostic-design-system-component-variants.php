<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Component Variant Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-component-variants
 * Training: https://wpshadow.com/training/design-system-component-variants
 */
class Diagnostic_Design_SYSTEM_COMPONENT_VARIANTS {
    public static function check() {
        return [
            'id' => 'design-system-component-variants',
            'title' => __('Component Variant Consistency', 'wpshadow'),
            'description' => __('Detects component usage without proper variants (missing sizes/states).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-component-variants',
            'training_link' => 'https://wpshadow.com/training/design-system-component-variants',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
