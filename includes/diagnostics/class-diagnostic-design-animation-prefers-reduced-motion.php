<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Animation Prefers Reduced Motion
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-animation-prefers-reduced-motion
 * Training: https://wpshadow.com/training/design-animation-prefers-reduced-motion
 */
class Diagnostic_Design_ANIMATION_PREFERS_REDUCED_MOTION {
    public static function check() {
        return [
            'id' => 'design-animation-prefers-reduced-motion',
            'title' => __('Animation Prefers Reduced Motion', 'wpshadow'),
            'description' => __('Confirms animations respect prefers-reduced-motion.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-animation-prefers-reduced-motion',
            'training_link' => 'https://wpshadow.com/training/design-animation-prefers-reduced-motion',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
