<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Pagination Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-pagination-consistency
 * Training: https://wpshadow.com/training/design-pagination-consistency
 */
class Diagnostic_Design_DESIGN_PAGINATION_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-pagination-consistency',
            'title' => __('Pagination Consistency', 'wpshadow'),
            'description' => __('Checks pagination alignment and states consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-pagination-consistency',
            'training_link' => 'https://wpshadow.com/training/design-pagination-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
