<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: DRY Component CSS
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-dry-component-css
 * Training: https://wpshadow.com/training/design-dry-component-css
 */
class Diagnostic_Design_DESIGN_DRY_COMPONENT_CSS {
    public static function check() {
        return [
            'id' => 'design-dry-component-css',
            'title' => __('DRY Component CSS', 'wpshadow'),
            'description' => __('Detects duplicate component CSS blocks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-dry-component-css',
            'training_link' => 'https://wpshadow.com/training/design-dry-component-css',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

