<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Customizer Default Values
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-theme-customizer-defaults
 * Training: https://wpshadow.com/training/design-theme-customizer-defaults
 */
class Diagnostic_Design_THEME_CUSTOMIZER_DEFAULTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-theme-customizer-defaults',
            'title' => __('Customizer Default Values', 'wpshadow'),
            'description' => __('Checks customizer default color/font sensible.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-theme-customizer-defaults',
            'training_link' => 'https://wpshadow.com/training/design-theme-customizer-defaults',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
