<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Menu Depth Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-menu-depth-l10n
 * Training: https://wpshadow.com/training/design-menu-depth-l10n
 */
class Diagnostic_Design_DESIGN_MENU_DEPTH_L10N {
    public static function check() {
        return [
            'id' => 'design-menu-depth-l10n',
            'title' => __('Menu Depth Localization', 'wpshadow'),
            'description' => __('Checks deep menus with long labels remain usable.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-menu-depth-l10n',
            'training_link' => 'https://wpshadow.com/training/design-menu-depth-l10n',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

