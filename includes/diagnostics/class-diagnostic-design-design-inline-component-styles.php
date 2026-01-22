<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Inline Component Styles
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-inline-component-styles
 * Training: https://wpshadow.com/training/design-inline-component-styles
 */
class Diagnostic_Design_DESIGN_INLINE_COMPONENT_STYLES {
    public static function check() {
        return [
            'id' => 'design-inline-component-styles',
            'title' => __('Inline Component Styles', 'wpshadow'),
            'description' => __('Flags inline styles on repeatable components.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-inline-component-styles',
            'training_link' => 'https://wpshadow.com/training/design-inline-component-styles',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

