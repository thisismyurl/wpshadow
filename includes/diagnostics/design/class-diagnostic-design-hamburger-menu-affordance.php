<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hamburger Menu Affordance
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-hamburger-menu-affordance
 * Training: https://wpshadow.com/training/design-hamburger-menu-affordance
 */
class Diagnostic_Design_HAMBURGER_MENU_AFFORDANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-hamburger-menu-affordance',
            'title' => __('Hamburger Menu Affordance', 'wpshadow'),
            'description' => __('Confirms hamburger icon obvious (3 lines clear).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hamburger-menu-affordance',
            'training_link' => 'https://wpshadow.com/training/design-hamburger-menu-affordance',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
