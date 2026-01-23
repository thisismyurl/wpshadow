<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Table Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-table-consistency
 * Training: https://wpshadow.com/training/design-vrt-table-consistency
 */
class Diagnostic_Design_DESIGN_VRT_TABLE_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-table-consistency',
            'title' => __('VRT Table Consistency', 'wpshadow'),
            'description' => __('Detects header/body alignment, zebra striping, and overflow regressions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-table-consistency',
            'training_link' => 'https://wpshadow.com/training/design-vrt-table-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}