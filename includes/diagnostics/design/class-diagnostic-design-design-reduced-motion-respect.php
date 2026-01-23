<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Reduced Motion Respect
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-reduced-motion-respect
 * Training: https://wpshadow.com/training/design-reduced-motion-respect
 */
class Diagnostic_Design_DESIGN_REDUCED_MOTION_RESPECT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-reduced-motion-respect',
            'title' => __('Reduced Motion Respect', 'wpshadow'),
            'description' => __('Checks honors prefers-reduced-motion with fallbacks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-reduced-motion-respect',
            'training_link' => 'https://wpshadow.com/training/design-reduced-motion-respect',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}