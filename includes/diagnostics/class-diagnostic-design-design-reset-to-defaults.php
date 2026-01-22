<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Reset To Defaults
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-reset-to-defaults
 * Training: https://wpshadow.com/training/design-reset-to-defaults
 */
class Diagnostic_Design_DESIGN_RESET_TO_DEFAULTS {
    public static function check() {
        return [
            'id' => 'design-reset-to-defaults',
            'title' => __('Reset To Defaults', 'wpshadow'),
            'description' => __('Checks reset restores tokenized defaults cleanly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-reset-to-defaults',
            'training_link' => 'https://wpshadow.com/training/design-reset-to-defaults',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

