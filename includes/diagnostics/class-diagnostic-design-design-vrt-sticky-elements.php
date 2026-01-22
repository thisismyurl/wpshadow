<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: VRT Sticky Elements
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-sticky-elements
 * Training: https://wpshadow.com/training/design-vrt-sticky-elements
 */
class Diagnostic_Design_DESIGN_VRT_STICKY_ELEMENTS {
    public static function check() {
        return [
            'id' => 'design-vrt-sticky-elements',
            'title' => __('VRT Sticky Elements', 'wpshadow'),
            'description' => __('Detects sticky header or TOC position and overlap regressions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-sticky-elements',
            'training_link' => 'https://wpshadow.com/training/design-vrt-sticky-elements',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

