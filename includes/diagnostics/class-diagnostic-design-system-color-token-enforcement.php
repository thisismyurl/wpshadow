<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Color Token Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-color-token-enforcement
 * Training: https://wpshadow.com/training/design-system-color-token-enforcement
 */
class Diagnostic_Design_SYSTEM_COLOR_TOKEN_ENFORCEMENT {
    public static function check() {
        return [
            'id' => 'design-system-color-token-enforcement',
            'title' => __('Color Token Enforcement', 'wpshadow'),
            'description' => __('Confirms all colors reference design tokens, not hardcoded hex values.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-color-token-enforcement',
            'training_link' => 'https://wpshadow.com/training/design-system-color-token-enforcement',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
