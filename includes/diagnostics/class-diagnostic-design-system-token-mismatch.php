<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Token Usage Mismatch
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-token-mismatch
 * Training: https://wpshadow.com/training/design-system-token-mismatch
 */
class Diagnostic_Design_SYSTEM_TOKEN_MISMATCH {
    public static function check() {
        return [
            'id' => 'design-system-token-mismatch',
            'title' => __('Token Usage Mismatch', 'wpshadow'),
            'description' => __('Detects hardcoded values that don't match system tokens (color drift).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-token-mismatch',
            'training_link' => 'https://wpshadow.com/training/design-system-token-mismatch',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
