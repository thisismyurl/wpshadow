<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network Woo Template Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-woo-template-drift
 * Training: https://wpshadow.com/training/design-network-woo-template-drift
 */
class Diagnostic_Design_DESIGN_NETWORK_WOO_TEMPLATE_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-woo-template-drift',
            'title' => __('Network Woo Template Drift', 'wpshadow'),
            'description' => __('Checks Woo templates style drift across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-woo-template-drift',
            'training_link' => 'https://wpshadow.com/training/design-network-woo-template-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
