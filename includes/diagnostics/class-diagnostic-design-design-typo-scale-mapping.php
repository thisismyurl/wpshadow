<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Typography Scale Mapping
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-typo-scale-mapping
 * Training: https://wpshadow.com/training/design-typo-scale-mapping
 */
class Diagnostic_Design_DESIGN_TYPO_SCALE_MAPPING {
    public static function check() {
        return [
            'id' => 'design-typo-scale-mapping',
            'title' => __('Typography Scale Mapping', 'wpshadow'),
            'description' => __('Checks customizer typography maps to the type ramp.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-typo-scale-mapping',
            'training_link' => 'https://wpshadow.com/training/design-typo-scale-mapping',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

