<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Touch Target Size Minimum
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-touch-target-size
 * Training: https://wpshadow.com/training/design-touch-target-size
 */
class Diagnostic_Design_TOUCH_TARGET_SIZE {
    public static function check() {
        return [
            'id' => 'design-touch-target-size',
            'title' => __('Touch Target Size Minimum', 'wpshadow'),
            'description' => __('Verifies all touch targets 44x44px minimum.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-touch-target-size',
            'training_link' => 'https://wpshadow.com/training/design-touch-target-size',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
