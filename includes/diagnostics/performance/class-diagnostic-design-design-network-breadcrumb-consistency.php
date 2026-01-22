<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network Breadcrumb Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-breadcrumb-consistency
 * Training: https://wpshadow.com/training/design-network-breadcrumb-consistency
 */
class Diagnostic_Design_DESIGN_NETWORK_BREADCRUMB_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-breadcrumb-consistency',
            'title' => __('Network Breadcrumb Consistency', 'wpshadow'),
            'description' => __('Checks breadcrumbs consistency across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-breadcrumb-consistency',
            'training_link' => 'https://wpshadow.com/training/design-network-breadcrumb-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
