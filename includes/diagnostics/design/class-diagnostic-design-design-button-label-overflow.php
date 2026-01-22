<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Button Label Overflow
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-button-label-overflow
 * Training: https://wpshadow.com/training/design-button-label-overflow
 */
class Diagnostic_Design_DESIGN_BUTTON_LABEL_OVERFLOW extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-button-label-overflow',
            'title' => __('Button Label Overflow', 'wpshadow'),
            'description' => __('Checks buttons with long labels to avoid wrapping or clipping.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-label-overflow',
            'training_link' => 'https://wpshadow.com/training/design-button-label-overflow',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
