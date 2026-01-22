<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Animation Performance Impact
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-animation-performance-impact
 * Training: https://wpshadow.com/training/design-animation-performance-impact
 */
class Diagnostic_Design_ANIMATION_PERFORMANCE_IMPACT {
    public static function check() {
        return [
            'id' => 'design-animation-performance-impact',
            'title' => __('Animation Performance Impact', 'wpshadow'),
            'description' => __('Verifies animations don't cause jank.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-animation-performance-impact',
            'training_link' => 'https://wpshadow.com/training/design-animation-performance-impact',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
