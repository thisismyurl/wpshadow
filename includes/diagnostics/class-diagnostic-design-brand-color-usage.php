<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Brand Color Usage
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-brand-color-usage
 * Training: https://wpshadow.com/training/design-brand-color-usage
 */
class Diagnostic_Design_BRAND_COLOR_USAGE {
    public static function check() {
        return [
            'id' => 'design-brand-color-usage',
            'title' => __('Brand Color Usage', 'wpshadow'),
            'description' => __('Checks primary brand color used consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-brand-color-usage',
            'training_link' => 'https://wpshadow.com/training/design-brand-color-usage',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
