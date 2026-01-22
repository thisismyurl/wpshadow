<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Active State Highlight Color
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-active-state-highlight-color
 * Training: https://wpshadow.com/training/design-active-state-highlight-color
 */
class Diagnostic_Design_ACTIVE_STATE_HIGHLIGHT_COLOR extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-active-state-highlight-color',
            'title' => __('Active State Highlight Color', 'wpshadow'),
            'description' => __('Validates active states use distinct color.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-active-state-highlight-color',
            'training_link' => 'https://wpshadow.com/training/design-active-state-highlight-color',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
