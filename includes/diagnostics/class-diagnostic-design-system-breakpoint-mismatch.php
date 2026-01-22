<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Breakpoint Mismatch Detection
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-breakpoint-mismatch
 * Training: https://wpshadow.com/training/design-system-breakpoint-mismatch
 */
class Diagnostic_Design_SYSTEM_BREAKPOINT_MISMATCH {
    public static function check() {
        return [
            'id' => 'design-system-breakpoint-mismatch',
            'title' => __('Breakpoint Mismatch Detection', 'wpshadow'),
            'description' => __('Finds CSS breakpoints that don't match design system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-breakpoint-mismatch',
            'training_link' => 'https://wpshadow.com/training/design-system-breakpoint-mismatch',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
