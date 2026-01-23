<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Editor Theme Support
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-editor-theme-support
 * Training: https://wpshadow.com/training/design-block-editor-theme-support
 */
class Diagnostic_Design_BLOCK_EDITOR_THEME_SUPPORT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-editor-theme-support',
            'title' => __('Block Editor Theme Support', 'wpshadow'),
            'description' => __('Verifies theme declares Gutenberg support properly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-editor-theme-support',
            'training_link' => 'https://wpshadow.com/training/design-block-editor-theme-support',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}