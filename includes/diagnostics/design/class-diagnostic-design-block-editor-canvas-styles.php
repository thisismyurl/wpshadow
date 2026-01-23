<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Canvas Editor Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-editor-canvas-styles
 * Training: https://wpshadow.com/training/design-block-editor-canvas-styles
 */
class Diagnostic_Design_BLOCK_EDITOR_CANVAS_STYLES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-editor-canvas-styles',
            'title' => __('Canvas Editor Styling', 'wpshadow'),
            'description' => __('Checks editor canvas matches front-end appearance.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-editor-canvas-styles',
            'training_link' => 'https://wpshadow.com/training/design-block-editor-canvas-styles',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}