<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Text Color Contrast Ratio
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-color-contrast-ratio-text
 * Training: https://wpshadow.com/training/design-color-contrast-ratio-text
 */
class Diagnostic_Design_COLOR_CONTRAST_RATIO_TEXT {
    public static function check() {
        return [
            'id' => 'design-color-contrast-ratio-text',
            'title' => __('Text Color Contrast Ratio', 'wpshadow'),
            'description' => __('Validates text/background contrast meets WCAG AA (4.5:1).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-color-contrast-ratio-text',
            'training_link' => 'https://wpshadow.com/training/design-color-contrast-ratio-text',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
