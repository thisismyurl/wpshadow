<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Font Licensing Compliance
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-brand-font-licensing
 * Training: https://wpshadow.com/training/design-brand-font-licensing
 */
class Diagnostic_Design_BRAND_FONT_LICENSING {
    public static function check() {
        return [
            'id' => 'design-brand-font-licensing',
            'title' => __('Font Licensing Compliance', 'wpshadow'),
            'description' => __('Confirms all custom fonts are properly licensed (Google Fonts, TypeKit, or commercial).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-brand-font-licensing',
            'training_link' => 'https://wpshadow.com/training/design-brand-font-licensing',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
