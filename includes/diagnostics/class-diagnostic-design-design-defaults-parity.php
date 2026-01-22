<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Defaults Parity
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-defaults-parity
 * Training: https://wpshadow.com/training/design-defaults-parity
 */
class Diagnostic_Design_DESIGN_DEFAULTS_PARITY {
    public static function check() {
        return [
            'id' => 'design-defaults-parity',
            'title' => __('Defaults Parity', 'wpshadow'),
            'description' => __('Checks defaults align with rendered CSS and theme.json.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-defaults-parity',
            'training_link' => 'https://wpshadow.com/training/design-defaults-parity',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

