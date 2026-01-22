<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Transform vs Top/Left
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-transform-vs-top-left
 * Training: https://wpshadow.com/training/design-transform-vs-top-left
 */
class Diagnostic_Design_DESIGN_TRANSFORM_VS_TOP_LEFT {
    public static function check() {
        return [
            'id' => 'design-transform-vs-top-left',
            'title' => __('Transform vs Top/Left', 'wpshadow'),
            'description' => __('Checks transforms are used for movement to avoid layout costs.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-transform-vs-top-left',
            'training_link' => 'https://wpshadow.com/training/design-transform-vs-top-left',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

