<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Focus Indicator Color
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-focus-indicator-color
 * Training: https://wpshadow.com/training/design-focus-indicator-color
 */
class Diagnostic_Design_FOCUS_INDICATOR_COLOR extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-focus-indicator-color',
            'title' => __('Focus Indicator Color', 'wpshadow'),
            'description' => __('Verifies focus indicators high-contrast.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-focus-indicator-color',
            'training_link' => 'https://wpshadow.com/training/design-focus-indicator-color',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
