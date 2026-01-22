<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Multisite Customizer Parity
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-multisite-customizer-parity
 * Training: https://wpshadow.com/training/design-multisite-customizer-parity
 */
class Diagnostic_Design_DESIGN_MULTISITE_CUSTOMIZER_PARITY {
    public static function check() {
        return [
            'id' => 'design-multisite-customizer-parity',
            'title' => __('Multisite Customizer Parity', 'wpshadow'),
            'description' => __('Detects site-level divergence from network defaults.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-multisite-customizer-parity',
            'training_link' => 'https://wpshadow.com/training/design-multisite-customizer-parity',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

