<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Navigation Menu Structure
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-navigation-menu-structure
 * Training: https://wpshadow.com/training/design-navigation-menu-structure
 */
class Diagnostic_Design_NAVIGATION_MENU_STRUCTURE {
    public static function check() {
        return [
            'id' => 'design-navigation-menu-structure',
            'title' => __('Navigation Menu Structure', 'wpshadow'),
            'description' => __('Checks main nav structured logically, clear hierarchy, max 7-9 items.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-navigation-menu-structure',
            'training_link' => 'https://wpshadow.com/training/design-navigation-menu-structure',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
