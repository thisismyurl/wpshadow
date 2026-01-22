<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Mobile Menu Scroll Behavior
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-mobile-menu-scroll-behavior
 * Training: https://wpshadow.com/training/design-mobile-menu-scroll-behavior
 */
class Diagnostic_Design_MOBILE_MENU_SCROLL_BEHAVIOR extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-mobile-menu-scroll-behavior',
            'title' => __('Mobile Menu Scroll Behavior', 'wpshadow'),
            'description' => __('Checks mobile menu scroll behavior.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-mobile-menu-scroll-behavior',
            'training_link' => 'https://wpshadow.com/training/design-mobile-menu-scroll-behavior',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
