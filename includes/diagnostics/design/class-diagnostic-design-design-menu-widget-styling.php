<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Menu and Widget Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-menu-widget-styling
 * Training: https://wpshadow.com/training/design-menu-widget-styling
 */
class Diagnostic_Design_DESIGN_MENU_WIDGET_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-menu-widget-styling',
            'title' => __('Menu and Widget Styling', 'wpshadow'),
            'description' => __('Checks menus and widgets are styled consistently via customizer.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-menu-widget-styling',
            'training_link' => 'https://wpshadow.com/training/design-menu-widget-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
