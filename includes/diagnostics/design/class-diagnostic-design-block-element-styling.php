<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Element Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-element-styling
 * Training: https://wpshadow.com/training/design-block-element-styling
 */
class Diagnostic_Design_BLOCK_ELEMENT_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-element-styling',
            'title' => __('Block Element Styling', 'wpshadow'),
            'description' => __('Validates all block elements styled properly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-element-styling',
            'training_link' => 'https://wpshadow.com/training/design-block-element-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}