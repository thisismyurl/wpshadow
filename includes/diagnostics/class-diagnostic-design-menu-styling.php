<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Custom Menu Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-menu-styling
 * Training: https://wpshadow.com/training/design-menu-styling
 */
class Diagnostic_Design_MENU_STYLING {
    public static function check() {
        return [
            'id' => 'design-menu-styling',
            'title' => __('Custom Menu Styling', 'wpshadow'),
            'description' => __('Verifies custom menus styled according to design system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-menu-styling',
            'training_link' => 'https://wpshadow.com/training/design-menu-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
