<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Network Type Ramp Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-type-ramp-drift
 * Training: https://wpshadow.com/training/design-network-type-ramp-drift
 */
class Diagnostic_Design_DESIGN_NETWORK_TYPE_RAMP_DRIFT {
    public static function check() {
        return [
            'id' => 'design-network-type-ramp-drift',
            'title' => __('Network Type Ramp Drift', 'wpshadow'),
            'description' => __('Measures typography ramp deltas across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-type-ramp-drift',
            'training_link' => 'https://wpshadow.com/training/design-network-type-ramp-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

