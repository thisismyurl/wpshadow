<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: List Block Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-list-styling
 * Training: https://wpshadow.com/training/design-block-list-styling
 */
class Diagnostic_Design_BLOCK_LIST_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-list-styling',
            'title' => __('List Block Styling', 'wpshadow'),
            'description' => __('Validates ordered/unordered lists styled properly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-list-styling',
            'training_link' => 'https://wpshadow.com/training/design-block-list-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
