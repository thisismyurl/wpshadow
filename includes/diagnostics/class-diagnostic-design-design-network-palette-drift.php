<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Network Palette Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-palette-drift
 * Training: https://wpshadow.com/training/design-network-palette-drift
 */
class Diagnostic_Design_DESIGN_NETWORK_PALETTE_DRIFT {
    public static function check() {
        return [
            'id' => 'design-network-palette-drift',
            'title' => __('Network Palette Drift', 'wpshadow'),
            'description' => __('Measures palette deltas across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-palette-drift',
            'training_link' => 'https://wpshadow.com/training/design-network-palette-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

