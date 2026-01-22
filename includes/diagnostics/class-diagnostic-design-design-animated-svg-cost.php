<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Animated SVG Cost
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-animated-svg-cost
 * Training: https://wpshadow.com/training/design-animated-svg-cost
 */
class Diagnostic_Design_DESIGN_ANIMATED_SVG_COST {
    public static function check() {
        return [
            'id' => 'design-animated-svg-cost',
            'title' => __('Animated SVG Cost', 'wpshadow'),
            'description' => __('Flags heavy SVG animations without optimization.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-animated-svg-cost',
            'training_link' => 'https://wpshadow.com/training/design-animated-svg-cost',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

