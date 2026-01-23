<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hover/Focus Shift Safety
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-hover-focus-shift
 * Training: https://wpshadow.com/training/design-hover-focus-shift
 */
class Diagnostic_Design_DESIGN_HOVER_FOCUS_SHIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-hover-focus-shift',
            'title' => __('Hover/Focus Shift Safety', 'wpshadow'),
            'description' => __('Checks hover or focus states avoid layout shift.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hover-focus-shift',
            'training_link' => 'https://wpshadow.com/training/design-hover-focus-shift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}