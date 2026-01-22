<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: VRT Spacing Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-spacing-drift
 * Training: https://wpshadow.com/training/design-vrt-spacing-drift
 */
class Diagnostic_Design_DESIGN_VRT_SPACING_DRIFT {
    public static function check() {
        return [
            'id' => 'design-vrt-spacing-drift',
            'title' => __('VRT Spacing Drift', 'wpshadow'),
            'description' => __('Detects padding and margin changes versus the grid baseline.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-spacing-drift',
            'training_link' => 'https://wpshadow.com/training/design-vrt-spacing-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

