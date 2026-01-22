<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Dark Mode Token Coverage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-dark-mode-tokens
 * Training: https://wpshadow.com/training/design-system-dark-mode-tokens
 */
class Diagnostic_Design_SYSTEM_DARK_MODE_TOKENS {
    public static function check() {
        return [
            'id' => 'design-system-dark-mode-tokens',
            'title' => __('Dark Mode Token Coverage', 'wpshadow'),
            'description' => __('Checks dark mode has token variants for all system colors.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-dark-mode-tokens',
            'training_link' => 'https://wpshadow.com/training/design-system-dark-mode-tokens',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
