<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network Asset Version Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-asset-version-drift
 * Training: https://wpshadow.com/training/design-network-asset-version-drift
 */
class Diagnostic_Design_DESIGN_NETWORK_ASSET_VERSION_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-asset-version-drift',
            'title' => __('Network Asset Version Drift', 'wpshadow'),
            'description' => __('Checks CSS/JS version alignment across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-asset-version-drift',
            'training_link' => 'https://wpshadow.com/training/design-network-asset-version-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}