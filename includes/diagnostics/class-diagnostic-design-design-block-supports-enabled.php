<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Block Supports Enabled
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-supports-enabled
 * Training: https://wpshadow.com/training/design-block-supports-enabled
 */
class Diagnostic_Design_DESIGN_BLOCK_SUPPORTS_ENABLED {
    public static function check() {
        return [
            'id' => 'design-block-supports-enabled',
            'title' => __('Block Supports Enabled', 'wpshadow'),
            'description' => __('Ensures spacing, color, typography, and border supports are declared.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-supports-enabled',
            'training_link' => 'https://wpshadow.com/training/design-block-supports-enabled',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

