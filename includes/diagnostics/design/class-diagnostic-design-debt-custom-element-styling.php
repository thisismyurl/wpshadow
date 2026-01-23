<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Custom Element Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-custom-element-styling
 * Training: https://wpshadow.com/training/design-debt-custom-element-styling
 */
class Diagnostic_Design_DEBT_CUSTOM_ELEMENT_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-custom-element-styling',
            'title' => __('Custom Element Styling', 'wpshadow'),
            'description' => __('Validates custom elements have baseline styles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-custom-element-styling',
            'training_link' => 'https://wpshadow.com/training/design-debt-custom-element-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}