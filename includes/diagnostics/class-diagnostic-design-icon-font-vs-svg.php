<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Icon Font vs SVG Decision
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-icon-font-vs-svg
 * Training: https://wpshadow.com/training/design-icon-font-vs-svg
 */
class Diagnostic_Design_ICON_FONT_VS_SVG {
    public static function check() {
        return [
            'id' => 'design-icon-font-vs-svg',
            'title' => __('Icon Font vs SVG Decision', 'wpshadow'),
            'description' => __('Confirms icon delivery method chosen.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-icon-font-vs-svg',
            'training_link' => 'https://wpshadow.com/training/design-icon-font-vs-svg',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
