<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Spacing Scale
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-spacing-scale
 * Training: https://wpshadow.com/training/design-block-spacing-scale
 */
class Diagnostic_Design_DESIGN_BLOCK_SPACING_SCALE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-spacing-scale',
            'title' => __('Block Spacing Scale', 'wpshadow'),
            'description' => __('Ensures block spacing controls map to the spacing scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-spacing-scale',
            'training_link' => 'https://wpshadow.com/training/design-block-spacing-scale',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}