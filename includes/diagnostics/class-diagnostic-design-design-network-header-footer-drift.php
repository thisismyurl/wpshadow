<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Network Header/Footer Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-header-footer-drift
 * Training: https://wpshadow.com/training/design-network-header-footer-drift
 */
class Diagnostic_Design_DESIGN_NETWORK_HEADER_FOOTER_DRIFT {
    public static function check() {
        return [
            'id' => 'design-network-header-footer-drift',
            'title' => __('Network Header/Footer Drift', 'wpshadow'),
            'description' => __('Checks header and footer consistency across the network.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-header-footer-drift',
            'training_link' => 'https://wpshadow.com/training/design-network-header-footer-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

