<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Network Font Asset Sharing
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-font-asset-sharing
 * Training: https://wpshadow.com/training/design-network-font-asset-sharing
 */
class Diagnostic_Design_DESIGN_NETWORK_FONT_ASSET_SHARING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-network-font-asset-sharing',
            'title' => __('Network Font Asset Sharing', 'wpshadow'),
            'description' => __('Checks shared font hosting and version alignment.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-font-asset-sharing',
            'training_link' => 'https://wpshadow.com/training/design-network-font-asset-sharing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}