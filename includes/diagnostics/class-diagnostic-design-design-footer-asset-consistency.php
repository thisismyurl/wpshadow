<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Footer Asset Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-footer-asset-consistency
 * Training: https://wpshadow.com/training/design-footer-asset-consistency
 */
class Diagnostic_Design_DESIGN_FOOTER_ASSET_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-footer-asset-consistency',
            'title' => __('Footer Asset Consistency', 'wpshadow'),
            'description' => __('Checks footer scripts and styles are consistent across templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-footer-asset-consistency',
            'training_link' => 'https://wpshadow.com/training/design-footer-asset-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

