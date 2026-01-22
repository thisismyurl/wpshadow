<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Mobile Menu Accessibility
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-mobile-menu-accessibility
 * Training: https://wpshadow.com/training/design-mobile-menu-accessibility
 */
class Diagnostic_Design_MOBILE_MENU_ACCESSIBILITY {
    public static function check() {
        return [
            'id' => 'design-mobile-menu-accessibility',
            'title' => __('Mobile Menu Accessibility', 'wpshadow'),
            'description' => __('Validates mobile menu accessible.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-mobile-menu-accessibility',
            'training_link' => 'https://wpshadow.com/training/design-mobile-menu-accessibility',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
