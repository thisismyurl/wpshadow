<?php declare(strict_types=1);
/**
 * Color Contrast Ratio Diagnostic
 *
 * Philosophy: Good contrast improves readability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Color_Contrast_Ratio {
    public static function check() {
        return [
            'id' => 'seo-color-contrast-ratio',
            'title' => 'Color Contrast for Readability',
            'description' => 'Maintain 4.5:1 contrast ratio for normal text, 3:1 for large text. Affects usability and accessibility.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/color-contrast/',
            'training_link' => 'https://wpshadow.com/training/visual-accessibility/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
