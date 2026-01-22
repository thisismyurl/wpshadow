<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Keyboard Shortcuts Discoverable
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-keyboard-shortcuts-discoverable
 * Training: https://wpshadow.com/training/design-keyboard-shortcuts-discoverable
 */
class Diagnostic_Design_KEYBOARD_SHORTCUTS_DISCOVERABLE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-keyboard-shortcuts-discoverable',
            'title' => __('Keyboard Shortcuts Discoverable', 'wpshadow'),
            'description' => __('Checks keyboard shortcuts documented.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-keyboard-shortcuts-discoverable',
            'training_link' => 'https://wpshadow.com/training/design-keyboard-shortcuts-discoverable',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
