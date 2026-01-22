<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Network Hero Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-hero-consistency
 * Training: https://wpshadow.com/training/design-network-hero-consistency
 */
class Diagnostic_Design_DESIGN_NETWORK_HERO_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-network-hero-consistency',
            'title' => __('Network Hero Consistency', 'wpshadow'),
            'description' => __('Checks hero sections alignment across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-hero-consistency',
            'training_link' => 'https://wpshadow.com/training/design-network-hero-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

