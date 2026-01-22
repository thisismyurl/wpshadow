<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Spacing Grid Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-spacing-grid-enforcement
 * Training: https://wpshadow.com/training/design-system-spacing-grid-enforcement
 */
class Diagnostic_Design_SYSTEM_SPACING_GRID_ENFORCEMENT {
    public static function check() {
        return [
            'id' => 'design-system-spacing-grid-enforcement',
            'title' => __('Spacing Grid Enforcement', 'wpshadow'),
            'description' => __('Confirms all margins/padding use design system scale (not random pixel values).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-spacing-grid-enforcement',
            'training_link' => 'https://wpshadow.com/training/design-system-spacing-grid-enforcement',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
