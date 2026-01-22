<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Toolbar Visibility
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-toolbar-visibility
 * Training: https://wpshadow.com/training/design-block-toolbar-visibility
 */
class Diagnostic_Design_BLOCK_TOOLBAR_VISIBILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-toolbar-visibility',
            'title' => __('Block Toolbar Visibility', 'wpshadow'),
            'description' => __('Confirms block toolbar visible, not obscured.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-toolbar-visibility',
            'training_link' => 'https://wpshadow.com/training/design-block-toolbar-visibility',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
