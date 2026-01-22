<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Design System Version Mismatch
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-version-mismatch
 * Training: https://wpshadow.com/training/design-system-version-mismatch
 */
class Diagnostic_Design_SYSTEM_VERSION_MISMATCH {
    public static function check() {
        return [
            'id' => 'design-system-version-mismatch',
            'title' => __('Design System Version Mismatch', 'wpshadow'),
            'description' => __('Detects code using outdated design system version.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-version-mismatch',
            'training_link' => 'https://wpshadow.com/training/design-system-version-mismatch',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
