<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Hamburger Menu Affordance Clear
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-hamburger-menu-affordance-clear
 * Training: https://wpshadow.com/training/design-hamburger-menu-affordance-clear
 */
class Diagnostic_Design_HAMBURGER_MENU_AFFORDANCE_CLEAR {
    public static function check() {
        return [
            'id' => 'design-hamburger-menu-affordance-clear',
            'title' => __('Hamburger Menu Affordance Clear', 'wpshadow'),
            'description' => __('Confirms hamburger icon obvious.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hamburger-menu-affordance-clear',
            'training_link' => 'https://wpshadow.com/training/design-hamburger-menu-affordance-clear',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
