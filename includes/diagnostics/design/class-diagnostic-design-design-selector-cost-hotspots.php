<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Selector Cost Hotspots
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-selector-cost-hotspots
 * Training: https://wpshadow.com/training/design-selector-cost-hotspots
 */
class Diagnostic_Design_DESIGN_SELECTOR_COST_HOTSPOTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-selector-cost-hotspots',
            'title' => __('Selector Cost Hotspots', 'wpshadow'),
            'description' => __('Flags heavy selectors including deep descendant and universal patterns.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-selector-cost-hotspots',
            'training_link' => 'https://wpshadow.com/training/design-selector-cost-hotspots',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
