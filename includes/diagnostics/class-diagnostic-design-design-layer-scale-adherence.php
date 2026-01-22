<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Layer Scale Adherence
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-layer-scale-adherence
 * Training: https://wpshadow.com/training/design-layer-scale-adherence
 */
class Diagnostic_Design_DESIGN_LAYER_SCALE_ADHERENCE {
    public static function check() {
        return [
            'id' => 'design-layer-scale-adherence',
            'title' => __('Layer Scale Adherence', 'wpshadow'),
            'description' => __('Flags z-index values outside the approved tiers.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-layer-scale-adherence',
            'training_link' => 'https://wpshadow.com/training/design-layer-scale-adherence',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

