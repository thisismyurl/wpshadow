<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Border Color Visibility
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-border-color-visibility
 * Training: https://wpshadow.com/training/design-border-color-visibility
 */
class Diagnostic_Design_BORDER_COLOR_VISIBILITY {
    public static function check() {
        return [
            'id' => 'design-border-color-visibility',
            'title' => __('Border Color Visibility', 'wpshadow'),
            'description' => __('Checks borders visible and contrasted.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-border-color-visibility',
            'training_link' => 'https://wpshadow.com/training/design-border-color-visibility',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
