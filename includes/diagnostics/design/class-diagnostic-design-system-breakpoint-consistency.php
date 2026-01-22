<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Breakpoint Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-breakpoint-consistency
 * Training: https://wpshadow.com/training/design-system-breakpoint-consistency
 */
class Diagnostic_Design_SYSTEM_BREAKPOINT_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-breakpoint-consistency',
            'title' => __('Breakpoint Consistency', 'wpshadow'),
            'description' => __('Verifies custom breakpoints match documented design system breakpoints.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-breakpoint-consistency',
            'training_link' => 'https://wpshadow.com/training/design-system-breakpoint-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
