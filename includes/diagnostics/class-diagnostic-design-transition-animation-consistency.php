<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Transition & Animation Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-transition-animation-consistency
 * Training: https://wpshadow.com/training/design-transition-animation-consistency
 */
class Diagnostic_Design_TRANSITION_ANIMATION_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-transition-animation-consistency',
            'title' => __('Transition & Animation Consistency', 'wpshadow'),
            'description' => __('Confirms animations use consistent timing (200ms-300ms), easing, don't exceed 500ms.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-transition-animation-consistency',
            'training_link' => 'https://wpshadow.com/training/design-transition-animation-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
