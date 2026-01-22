<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Nesting Depth Overuse
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-nesting-depth
 * Training: https://wpshadow.com/training/design-nesting-depth
 */
class Diagnostic_Design_DESIGN_NESTING_DEPTH {
    public static function check() {
        return [
            'id' => 'design-nesting-depth',
            'title' => __('Nesting Depth Overuse', 'wpshadow'),
            'description' => __('Flags excessive selector nesting depth.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-nesting-depth',
            'training_link' => 'https://wpshadow.com/training/design-nesting-depth',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

