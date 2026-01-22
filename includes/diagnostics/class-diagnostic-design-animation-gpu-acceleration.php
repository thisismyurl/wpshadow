<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Animation GPU Acceleration
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-animation-gpu-acceleration
 * Training: https://wpshadow.com/training/design-animation-gpu-acceleration
 */
class Diagnostic_Design_ANIMATION_GPU_ACCELERATION {
    public static function check() {
        return [
            'id' => 'design-animation-gpu-acceleration',
            'title' => __('Animation GPU Acceleration', 'wpshadow'),
            'description' => __('Validates animations GPU-accelerated.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-animation-gpu-acceleration',
            'training_link' => 'https://wpshadow.com/training/design-animation-gpu-acceleration',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
