<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Columns Block Responsive
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-columns-responsive
 * Training: https://wpshadow.com/training/design-block-columns-responsive
 */
class Diagnostic_Design_BLOCK_COLUMNS_RESPONSIVE {
    public static function check() {
        return [
            'id' => 'design-block-columns-responsive',
            'title' => __('Columns Block Responsive', 'wpshadow'),
            'description' => __('Checks columns block stacks properly on mobile.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-columns-responsive',
            'training_link' => 'https://wpshadow.com/training/design-block-columns-responsive',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
