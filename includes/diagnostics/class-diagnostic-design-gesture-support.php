<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Gesture Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-gesture-support
 * Training: https://wpshadow.com/training/design-gesture-support
 */
class Diagnostic_Design_GESTURE_SUPPORT {
    public static function check() {
        return [
            'id' => 'design-gesture-support',
            'title' => __('Gesture Support', 'wpshadow'),
            'description' => __('Confirms touch gestures implemented.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-gesture-support',
            'training_link' => 'https://wpshadow.com/training/design-gesture-support',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
