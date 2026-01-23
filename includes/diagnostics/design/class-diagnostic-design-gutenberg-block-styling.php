<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Gutenberg Block Style Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-gutenberg-block-styling
 * Training: https://wpshadow.com/training/design-gutenberg-block-styling
 */
class Diagnostic_Design_GUTENBERG_BLOCK_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-gutenberg-block-styling',
            'title' => __('Gutenberg Block Style Consistency', 'wpshadow'),
            'description' => __('Verifies custom Gutenberg blocks styled consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-gutenberg-block-styling',
            'training_link' => 'https://wpshadow.com/training/design-gutenberg-block-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}