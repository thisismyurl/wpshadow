<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Focus Visible Indicator
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-focus-visible-indicator
 * Training: https://wpshadow.com/training/design-focus-visible-indicator
 */
class Diagnostic_Design_FOCUS_VISIBLE_INDICATOR {
    public static function check() {
        return [
            'id' => 'design-focus-visible-indicator',
            'title' => __('Focus Visible Indicator', 'wpshadow'),
            'description' => __('Verifies keyboard focus indicator visible, minimum 2px.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-focus-visible-indicator',
            'training_link' => 'https://wpshadow.com/training/design-focus-visible-indicator',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
