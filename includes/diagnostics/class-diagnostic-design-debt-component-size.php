<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Component Bundle Size
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-component-size
 * Training: https://wpshadow.com/training/design-debt-component-size
 */
class Diagnostic_Design_DEBT_COMPONENT_SIZE {
    public static function check() {
        return [
            'id' => 'design-debt-component-size',
            'title' => __('Component Bundle Size', 'wpshadow'),
            'description' => __('Detects bloated components, split candidates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-component-size',
            'training_link' => 'https://wpshadow.com/training/design-debt-component-size',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
