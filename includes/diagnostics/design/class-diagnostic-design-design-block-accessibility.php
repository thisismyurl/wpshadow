<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Accessibility
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-accessibility
 * Training: https://wpshadow.com/training/design-block-accessibility
 */
class Diagnostic_Design_DESIGN_BLOCK_ACCESSIBILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-accessibility',
            'title' => __('Block Accessibility', 'wpshadow'),
            'description' => __('Checks focus states and keyboard navigation in block controls.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-accessibility',
            'training_link' => 'https://wpshadow.com/training/design-block-accessibility',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
