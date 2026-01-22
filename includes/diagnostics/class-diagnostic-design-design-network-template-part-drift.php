<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Network Template Part Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-template-part-drift
 * Training: https://wpshadow.com/training/design-network-template-part-drift
 */
class Diagnostic_Design_DESIGN_NETWORK_TEMPLATE_PART_DRIFT {
    public static function check() {
        return [
            'id' => 'design-network-template-part-drift',
            'title' => __('Network Template Part Drift', 'wpshadow'),
            'description' => __('Diffs key template parts across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-template-part-drift',
            'training_link' => 'https://wpshadow.com/training/design-network-template-part-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

