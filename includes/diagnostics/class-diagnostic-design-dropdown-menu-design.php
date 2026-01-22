<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Dropdown Menu Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-dropdown-menu-design
 * Training: https://wpshadow.com/training/design-dropdown-menu-design
 */
class Diagnostic_Design_DROPDOWN_MENU_DESIGN {
    public static function check() {
        return [
            'id' => 'design-dropdown-menu-design',
            'title' => __('Dropdown Menu Design', 'wpshadow'),
            'description' => __('Verifies dropdowns show hover/focus states, keyboard navigation.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-dropdown-menu-design',
            'training_link' => 'https://wpshadow.com/training/design-dropdown-menu-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
