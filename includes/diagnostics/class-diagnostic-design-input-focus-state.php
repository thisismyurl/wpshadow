<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Input Focus State
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-input-focus-state
 * Training: https://wpshadow.com/training/design-input-focus-state
 */
class Diagnostic_Design_INPUT_FOCUS_STATE {
    public static function check() {
        return [
            'id' => 'design-input-focus-state',
            'title' => __('Input Focus State', 'wpshadow'),
            'description' => __('Confirms focused inputs have visible indicator.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-input-focus-state',
            'training_link' => 'https://wpshadow.com/training/design-input-focus-state',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
