<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Responsive Behavior
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-gutenberg-block-responsive
 * Training: https://wpshadow.com/training/design-gutenberg-block-responsive
 */
class Diagnostic_Design_GUTENBERG_BLOCK_RESPONSIVE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-gutenberg-block-responsive',
            'title' => __('Block Responsive Behavior', 'wpshadow'),
            'description' => __('Checks Gutenberg blocks responsive at all breakpoints.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-gutenberg-block-responsive',
            'training_link' => 'https://wpshadow.com/training/design-gutenberg-block-responsive',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}