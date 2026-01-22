<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Button Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-button-consistency
 * Training: https://wpshadow.com/training/design-block-button-consistency
 */
class Diagnostic_Design_DESIGN_BLOCK_BUTTON_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-button-consistency',
            'title' => __('Block Button Consistency', 'wpshadow'),
            'description' => __('Ensures the core button block matches the component system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-button-consistency',
            'training_link' => 'https://wpshadow.com/training/design-block-button-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
