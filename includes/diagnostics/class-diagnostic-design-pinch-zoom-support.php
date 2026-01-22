<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Pinch Zoom Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-pinch-zoom-support
 * Training: https://wpshadow.com/training/design-pinch-zoom-support
 */
class Diagnostic_Design_PINCH_ZOOM_SUPPORT {
    public static function check() {
        return [
            'id' => 'design-pinch-zoom-support',
            'title' => __('Pinch Zoom Support', 'wpshadow'),
            'description' => __('Confirms pinch-zoom functional.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-pinch-zoom-support',
            'training_link' => 'https://wpshadow.com/training/design-pinch-zoom-support',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
