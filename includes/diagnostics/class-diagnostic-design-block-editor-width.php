<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Block Editor Content Width
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-editor-width
 * Training: https://wpshadow.com/training/design-block-editor-width
 */
class Diagnostic_Design_BLOCK_EDITOR_WIDTH {
    public static function check() {
        return [
            'id' => 'design-block-editor-width',
            'title' => __('Block Editor Content Width', 'wpshadow'),
            'description' => __('Validates max-width matches front-end.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-editor-width',
            'training_link' => 'https://wpshadow.com/training/design-block-editor-width',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
