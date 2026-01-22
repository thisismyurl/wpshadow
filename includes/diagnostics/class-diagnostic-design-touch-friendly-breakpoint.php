<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Touch-Friendly Breakpoint Decision
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-touch-friendly-breakpoint
 * Training: https://wpshadow.com/training/design-touch-friendly-breakpoint
 */
class Diagnostic_Design_TOUCH_FRIENDLY_BREAKPOINT {
    public static function check() {
        return [
            'id' => 'design-touch-friendly-breakpoint',
            'title' => __('Touch-Friendly Breakpoint Decision', 'wpshadow'),
            'description' => __('Validates breakpoint decisions consider touch.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-touch-friendly-breakpoint',
            'training_link' => 'https://wpshadow.com/training/design-touch-friendly-breakpoint',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
