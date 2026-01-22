<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Disabled Element Color
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-disabled-element-color
 * Training: https://wpshadow.com/training/design-disabled-element-color
 */
class Diagnostic_Design_DISABLED_ELEMENT_COLOR {
    public static function check() {
        return [
            'id' => 'design-disabled-element-color',
            'title' => __('Disabled Element Color', 'wpshadow'),
            'description' => __('Verifies disabled elements visually distinct.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-disabled-element-color',
            'training_link' => 'https://wpshadow.com/training/design-disabled-element-color',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
