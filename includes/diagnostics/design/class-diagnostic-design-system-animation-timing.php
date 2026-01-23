<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Animation Timing Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-animation-timing
 * Training: https://wpshadow.com/training/design-system-animation-timing
 */
class Diagnostic_Design_SYSTEM_ANIMATION_TIMING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-animation-timing',
            'title' => __('Animation Timing Enforcement', 'wpshadow'),
            'description' => __('Confirms animations use system timing (200ms, 300ms, 500ms scale).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-animation-timing',
            'training_link' => 'https://wpshadow.com/training/design-system-animation-timing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}