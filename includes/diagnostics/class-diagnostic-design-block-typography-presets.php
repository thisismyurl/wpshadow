<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Typography Presets
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-typography-presets
 * Training: https://wpshadow.com/training/design-block-typography-presets
 */
class Diagnostic_Design_BLOCK_TYPOGRAPHY_PRESETS {
    public static function check() {
        return [
            'id' => 'design-block-typography-presets',
            'title' => __('Typography Presets', 'wpshadow'),
            'description' => __('Validates font size/style presets configured.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-typography-presets',
            'training_link' => 'https://wpshadow.com/training/design-block-typography-presets',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
