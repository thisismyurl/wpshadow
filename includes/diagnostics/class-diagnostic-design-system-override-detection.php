<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: System Override Detection
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-override-detection
 * Training: https://wpshadow.com/training/design-system-override-detection
 */
class Diagnostic_Design_SYSTEM_OVERRIDE_DETECTION {
    public static function check() {
        return [
            'id' => 'design-system-override-detection',
            'title' => __('System Override Detection', 'wpshadow'),
            'description' => __('Finds !important overrides of design system styles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-override-detection',
            'training_link' => 'https://wpshadow.com/training/design-system-override-detection',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
