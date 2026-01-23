<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Grid Alignment Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-grid-alignment
 * Training: https://wpshadow.com/training/design-grid-alignment
 */
class Diagnostic_Design_DESIGN_GRID_ALIGNMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-grid-alignment',
            'title' => __('Grid Alignment Consistency', 'wpshadow'),
            'description' => __('Flags off-grid spacing, mixed units, and inconsistent gaps.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-grid-alignment',
            'training_link' => 'https://wpshadow.com/training/design-grid-alignment',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}