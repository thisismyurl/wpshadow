<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Color Proliferation
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-color-proliferation
 * Training: https://wpshadow.com/training/design-color-proliferation
 */
class Diagnostic_Design_DESIGN_COLOR_PROLIFERATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-color-proliferation',
            'title' => __('Color Proliferation', 'wpshadow'),
            'description' => __('Counts unique colors and flags near-duplicates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-color-proliferation',
            'training_link' => 'https://wpshadow.com/training/design-color-proliferation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}