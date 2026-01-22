<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Color Palette Setup
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-color-palette
 * Training: https://wpshadow.com/training/design-block-color-palette
 */
class Diagnostic_Design_BLOCK_COLOR_PALETTE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-color-palette',
            'title' => __('Block Color Palette Setup', 'wpshadow'),
            'description' => __('Checks theme color palette exposed to Gutenberg.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-color-palette',
            'training_link' => 'https://wpshadow.com/training/design-block-color-palette',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
