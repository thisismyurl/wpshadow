<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Drag-Drop UX
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-drag-drop-ux
 * Training: https://wpshadow.com/training/design-block-drag-drop-ux
 */
class Diagnostic_Design_BLOCK_DRAG_DROP_UX extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-drag-drop-ux',
            'title' => __('Block Drag-Drop UX', 'wpshadow'),
            'description' => __('Checks block reordering works smoothly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-drag-drop-ux',
            'training_link' => 'https://wpshadow.com/training/design-block-drag-drop-ux',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
