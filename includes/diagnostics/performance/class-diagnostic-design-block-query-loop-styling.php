<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Query Loop Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-query-loop-styling
 * Training: https://wpshadow.com/training/design-block-query-loop-styling
 */
class Diagnostic_Design_BLOCK_QUERY_LOOP_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-query-loop-styling',
            'title' => __('Query Loop Styling', 'wpshadow'),
            'description' => __('Validates query loop results styled properly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-query-loop-styling',
            'training_link' => 'https://wpshadow.com/training/design-block-query-loop-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
