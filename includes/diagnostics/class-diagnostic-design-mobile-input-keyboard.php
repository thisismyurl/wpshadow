<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Mobile Input Keyboard Type
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-mobile-input-keyboard
 * Training: https://wpshadow.com/training/design-mobile-input-keyboard
 */
class Diagnostic_Design_MOBILE_INPUT_KEYBOARD {
    public static function check() {
        return [
            'id' => 'design-mobile-input-keyboard',
            'title' => __('Mobile Input Keyboard Type', 'wpshadow'),
            'description' => __('Validates input type appropriate.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-mobile-input-keyboard',
            'training_link' => 'https://wpshadow.com/training/design-mobile-input-keyboard',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
