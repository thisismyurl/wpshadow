<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Table Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-table-consistency
 * Training: https://wpshadow.com/training/design-table-consistency
 */
class Diagnostic_Design_DESIGN_TABLE_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-table-consistency',
            'title' => __('Table Consistency', 'wpshadow'),
            'description' => __('Checks table headers, rows, padding, and borders consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-table-consistency',
            'training_link' => 'https://wpshadow.com/training/design-table-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
