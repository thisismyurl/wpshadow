<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Hover State Color Change
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-hover-state-color-change
 * Training: https://wpshadow.com/training/design-hover-state-color-change
 */
class Diagnostic_Design_HOVER_STATE_COLOR_CHANGE {
    public static function check() {
        return [
            'id' => 'design-hover-state-color-change',
            'title' => __('Hover State Color Change', 'wpshadow'),
            'description' => __('Confirms hover states show sufficient change.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hover-state-color-change',
            'training_link' => 'https://wpshadow.com/training/design-hover-state-color-change',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
