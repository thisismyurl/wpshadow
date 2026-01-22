<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Radius Scale Adherence
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-radius-scale-adherence
 * Training: https://wpshadow.com/training/design-radius-scale-adherence
 */
class Diagnostic_Design_DESIGN_RADIUS_SCALE_ADHERENCE {
    public static function check() {
        return [
            'id' => 'design-radius-scale-adherence',
            'title' => __('Radius Scale Adherence', 'wpshadow'),
            'description' => __('Flags radii that are not on the radius scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-radius-scale-adherence',
            'training_link' => 'https://wpshadow.com/training/design-radius-scale-adherence',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

