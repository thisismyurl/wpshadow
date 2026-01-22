<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Toggle Switch Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-toggle-switch-design
 * Training: https://wpshadow.com/training/design-toggle-switch-design
 */
class Diagnostic_Design_TOGGLE_SWITCH_DESIGN {
    public static function check() {
        return [
            'id' => 'design-toggle-switch-design',
            'title' => __('Toggle Switch Design', 'wpshadow'),
            'description' => __('Checks toggle switches 40-50px width.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-toggle-switch-design',
            'training_link' => 'https://wpshadow.com/training/design-toggle-switch-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
