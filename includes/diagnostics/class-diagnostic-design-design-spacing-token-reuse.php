<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Spacing Token Reuse
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-spacing-token-reuse
 * Training: https://wpshadow.com/training/design-spacing-token-reuse
 */
class Diagnostic_Design_DESIGN_SPACING_TOKEN_REUSE {
    public static function check() {
        return [
            'id' => 'design-spacing-token-reuse',
            'title' => __('Spacing Token Reuse', 'wpshadow'),
            'description' => __('Checks components use spacing tokens instead of ad-hoc values.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-spacing-token-reuse',
            'training_link' => 'https://wpshadow.com/training/design-spacing-token-reuse',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

