<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Font Subsetting Strategy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-font-subsetting
 * Training: https://wpshadow.com/training/design-font-subsetting
 */
class Diagnostic_Design_FONT_SUBSETTING {
    public static function check() {
        return [
            'id' => 'design-font-subsetting',
            'title' => __('Font Subsetting Strategy', 'wpshadow'),
            'description' => __('Checks fonts subset to Latin/necessary characters.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-subsetting',
            'training_link' => 'https://wpshadow.com/training/design-font-subsetting',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
