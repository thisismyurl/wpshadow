<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Important Density
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-important-density
 * Training: https://wpshadow.com/training/design-important-density
 */
class Diagnostic_Design_DESIGN_IMPORTANT_DENSITY {
    public static function check() {
        return [
            'id' => 'design-important-density',
            'title' => __('Important Density', 'wpshadow'),
            'description' => __('Flags high density of !important usage.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-important-density',
            'training_link' => 'https://wpshadow.com/training/design-important-density',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

