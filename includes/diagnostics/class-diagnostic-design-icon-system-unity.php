<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Icon System Unity
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-icon-system-unity
 * Training: https://wpshadow.com/training/design-icon-system-unity
 */
class Diagnostic_Design_ICON_SYSTEM_UNITY {
    public static function check() {
        return [
            'id' => 'design-icon-system-unity',
            'title' => __('Icon System Unity', 'wpshadow'),
            'description' => __('Checks if icons follow consistent stroke width, size scale, and style (line vs filled).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-icon-system-unity',
            'training_link' => 'https://wpshadow.com/training/design-icon-system-unity',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
