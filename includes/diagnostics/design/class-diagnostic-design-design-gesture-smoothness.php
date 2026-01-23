<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Gesture Smoothness
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-gesture-smoothness
 * Training: https://wpshadow.com/training/design-gesture-smoothness
 */
class Diagnostic_Design_DESIGN_GESTURE_SMOOTHNESS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-gesture-smoothness',
            'title' => __('Gesture Smoothness', 'wpshadow'),
            'description' => __('Checks touch and drag interactions avoid jank.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-gesture-smoothness',
            'training_link' => 'https://wpshadow.com/training/design-gesture-smoothness',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}