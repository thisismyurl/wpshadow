<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Keyboard Navigation Complete
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-keyboard-navigation-complete
 * Training: https://wpshadow.com/training/design-keyboard-navigation-complete
 */
class Diagnostic_Design_KEYBOARD_NAVIGATION_COMPLETE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-keyboard-navigation-complete',
            'title' => __('Keyboard Navigation Complete', 'wpshadow'),
            'description' => __('Validates site fully navigable via keyboard.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-keyboard-navigation-complete',
            'training_link' => 'https://wpshadow.com/training/design-keyboard-navigation-complete',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}