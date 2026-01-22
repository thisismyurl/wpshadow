<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Network Nav Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-nav-consistency
 * Training: https://wpshadow.com/training/design-network-nav-consistency
 */
class Diagnostic_Design_DESIGN_NETWORK_NAV_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-network-nav-consistency',
            'title' => __('Network Nav Consistency', 'wpshadow'),
            'description' => __('Checks navigation pattern consistency across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-nav-consistency',
            'training_link' => 'https://wpshadow.com/training/design-network-nav-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

