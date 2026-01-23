<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Custom Property Inventory
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-custom-property-count
 * Training: https://wpshadow.com/training/design-debt-custom-property-count
 */
class Diagnostic_Design_DEBT_CUSTOM_PROPERTY_COUNT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-custom-property-count',
            'title' => __('Custom Property Inventory', 'wpshadow'),
            'description' => __('Counts CSS custom properties (should match system).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-custom-property-count',
            'training_link' => 'https://wpshadow.com/training/design-debt-custom-property-count',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}