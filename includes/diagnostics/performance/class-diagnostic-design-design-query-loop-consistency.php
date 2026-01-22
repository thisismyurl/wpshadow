<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Query Loop Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-query-loop-consistency
 * Training: https://wpshadow.com/training/design-query-loop-consistency
 */
class Diagnostic_Design_DESIGN_QUERY_LOOP_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-query-loop-consistency',
            'title' => __('Query Loop Consistency', 'wpshadow'),
            'description' => __('Checks card/query loop styling consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-query-loop-consistency',
            'training_link' => 'https://wpshadow.com/training/design-query-loop-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
