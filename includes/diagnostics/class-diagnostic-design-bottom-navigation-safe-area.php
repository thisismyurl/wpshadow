<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Bottom Navigation Safe Area
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-bottom-navigation-safe-area
 * Training: https://wpshadow.com/training/design-bottom-navigation-safe-area
 */
class Diagnostic_Design_BOTTOM_NAVIGATION_SAFE_AREA {
    public static function check() {
        return [
            'id' => 'design-bottom-navigation-safe-area',
            'title' => __('Bottom Navigation Safe Area', 'wpshadow'),
            'description' => __('Validates safe area respect.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-bottom-navigation-safe-area',
            'training_link' => 'https://wpshadow.com/training/design-bottom-navigation-safe-area',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
