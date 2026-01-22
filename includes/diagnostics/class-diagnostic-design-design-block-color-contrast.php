<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Block Color Contrast
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-color-contrast
 * Training: https://wpshadow.com/training/design-block-color-contrast
 */
class Diagnostic_Design_DESIGN_BLOCK_COLOR_CONTRAST {
    public static function check() {
        return [
            'id' => 'design-block-color-contrast',
            'title' => __('Block Color Contrast', 'wpshadow'),
            'description' => __('Ensures block text/background meet contrast requirements.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-color-contrast',
            'training_link' => 'https://wpshadow.com/training/design-block-color-contrast',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

