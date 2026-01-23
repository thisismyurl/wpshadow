<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network Spacing Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-spacing-drift
 * Training: https://wpshadow.com/training/design-network-spacing-drift
 */
class Diagnostic_Design_DESIGN_NETWORK_SPACING_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-spacing-drift',
            'title' => __('Network Spacing Drift', 'wpshadow'),
            'description' => __('Measures spacing scale deltas across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-spacing-drift',
            'training_link' => 'https://wpshadow.com/training/design-network-spacing-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}