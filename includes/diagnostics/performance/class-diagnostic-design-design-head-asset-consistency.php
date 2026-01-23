<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Head Asset Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-head-asset-consistency
 * Training: https://wpshadow.com/training/design-head-asset-consistency
 */
class Diagnostic_Design_DESIGN_HEAD_ASSET_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-head-asset-consistency',
            'title' => __('Head Asset Consistency', 'wpshadow'),
            'description' => __('Checks critical CSS and preloads are consistent across templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-head-asset-consistency',
            'training_link' => 'https://wpshadow.com/training/design-head-asset-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}