<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Sidebar Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-sidebar-consistency
 * Training: https://wpshadow.com/training/design-sidebar-consistency
 */
class Diagnostic_Design_DESIGN_SIDEBAR_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-sidebar-consistency',
            'title' => __('Sidebar Consistency', 'wpshadow'),
            'description' => __('Checks sidebar width, gaps, and widget styling consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-sidebar-consistency',
            'training_link' => 'https://wpshadow.com/training/design-sidebar-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

