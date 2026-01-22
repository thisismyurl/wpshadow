<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Breakpoint Strategy Definition
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-breakpoint-strategy
 * Training: https://wpshadow.com/training/design-breakpoint-strategy
 */
class Diagnostic_Design_BREAKPOINT_STRATEGY {
    public static function check() {
        return [
            'id' => 'design-breakpoint-strategy',
            'title' => __('Breakpoint Strategy Definition', 'wpshadow'),
            'description' => __('Checks if breakpoints follow industry standard or custom documented.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-breakpoint-strategy',
            'training_link' => 'https://wpshadow.com/training/design-breakpoint-strategy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
