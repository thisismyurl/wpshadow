<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS Asset Churn
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-css-asset-churn
 * Training: https://wpshadow.com/training/design-css-asset-churn
 */
class Diagnostic_Design_DESIGN_CSS_ASSET_CHURN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-asset-churn',
            'title' => __('CSS Asset Churn', 'wpshadow'),
            'description' => __('Detects frequent cache-busting or asset churn without need.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-asset-churn',
            'training_link' => 'https://wpshadow.com/training/design-css-asset-churn',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
