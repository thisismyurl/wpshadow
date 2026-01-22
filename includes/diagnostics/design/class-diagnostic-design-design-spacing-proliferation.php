<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Spacing Proliferation
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-spacing-proliferation
 * Training: https://wpshadow.com/training/design-spacing-proliferation
 */
class Diagnostic_Design_DESIGN_SPACING_PROLIFERATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-spacing-proliferation',
            'title' => __('Spacing Proliferation', 'wpshadow'),
            'description' => __('Counts unique spacing values; flags off-scale margins and paddings.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-spacing-proliferation',
            'training_link' => 'https://wpshadow.com/training/design-spacing-proliferation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
