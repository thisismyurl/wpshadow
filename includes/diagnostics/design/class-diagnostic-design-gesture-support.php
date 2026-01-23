<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Gesture Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-gesture-support
 * Training: https://wpshadow.com/training/design-gesture-support
 */
class Diagnostic_Design_GESTURE_SUPPORT extends Diagnostic_Base {
    public static function check(): ?array {
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