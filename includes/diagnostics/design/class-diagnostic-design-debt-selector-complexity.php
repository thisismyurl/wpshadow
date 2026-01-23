<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Selector Complexity Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-selector-complexity
 * Training: https://wpshadow.com/training/design-debt-selector-complexity
 */
class Diagnostic_Design_DEBT_SELECTOR_COMPLEXITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-selector-complexity',
            'title' => __('Selector Complexity Score', 'wpshadow'),
            'description' => __('Measures selector specificity depth (avoid nesting).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-selector-complexity',
            'training_link' => 'https://wpshadow.com/training/design-debt-selector-complexity',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}