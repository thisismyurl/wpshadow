<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Custom Block Registration
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-custom-blocks
 * Training: https://wpshadow.com/training/design-block-custom-blocks
 */
class Diagnostic_Design_BLOCK_CUSTOM_BLOCKS {
    public static function check() {
        return [
            'id' => 'design-block-custom-blocks',
            'title' => __('Custom Block Registration', 'wpshadow'),
            'description' => __('Verifies custom blocks properly registered.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-custom-blocks',
            'training_link' => 'https://wpshadow.com/training/design-block-custom-blocks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
