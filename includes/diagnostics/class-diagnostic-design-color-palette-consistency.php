<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Color Palette Consistency
 * Philosophy: Inspire confidence (#8) with unified visual brand; Show value (#9) by measuring consistency
 * KB Link: https://wpshadow.com/kb/color-palette-consistency
 * Training: https://wpshadow.com/training/design-color-systems
 */
class Diagnostic_Design_Color_Palette_Consistency {
    public static function check() {
        return [
            'id' => 'design-color-palette-consistency',
            'title' => __('Color Palette Consistency', 'wpshadow'),
            'description' => __('Identifies colors used across site and verifies they comply with defined brand palette. Flags rogue colors outside primary/secondary/accent system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/color-palette-consistency',
            'training_link' => 'https://wpshadow.com/training/design-color-systems',
            'auto_fixable' => false,
            'threat_level' => 4
        ];
    }
}
