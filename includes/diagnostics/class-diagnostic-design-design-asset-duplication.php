<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Asset Duplication
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-asset-duplication
 * Training: https://wpshadow.com/training/design-asset-duplication
 */
class Diagnostic_Design_DESIGN_ASSET_DUPLICATION {
    public static function check() {
        return [
            'id' => 'design-asset-duplication',
            'title' => __('Asset Duplication', 'wpshadow'),
            'description' => __('Detects duplicated SVGs, icons, or fonts across bundles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-asset-duplication',
            'training_link' => 'https://wpshadow.com/training/design-asset-duplication',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

