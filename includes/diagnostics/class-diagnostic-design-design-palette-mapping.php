<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Palette Mapping
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-palette-mapping
 * Training: https://wpshadow.com/training/design-palette-mapping
 */
class Diagnostic_Design_DESIGN_PALETTE_MAPPING {
    public static function check() {
        return [
            'id' => 'design-palette-mapping',
            'title' => __('Palette Mapping', 'wpshadow'),
            'description' => __('Checks customizer palettes map to theme.json tokens.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-palette-mapping',
            'training_link' => 'https://wpshadow.com/training/design-palette-mapping',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

