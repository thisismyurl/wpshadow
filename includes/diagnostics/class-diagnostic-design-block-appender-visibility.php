<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Block Appender Visibility
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-appender-visibility
 * Training: https://wpshadow.com/training/design-block-appender-visibility
 */
class Diagnostic_Design_BLOCK_APPENDER_VISIBILITY {
    public static function check() {
        return [
            'id' => 'design-block-appender-visibility',
            'title' => __('Block Appender Visibility', 'wpshadow'),
            'description' => __('Checks + button for new blocks clearly visible.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-appender-visibility',
            'training_link' => 'https://wpshadow.com/training/design-block-appender-visibility',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
