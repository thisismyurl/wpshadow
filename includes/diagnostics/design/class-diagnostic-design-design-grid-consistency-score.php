<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Grid Consistency Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-grid-consistency-score
 * Training: https://wpshadow.com/training/design-grid-consistency-score
 */
class Diagnostic_Design_DESIGN_GRID_CONSISTENCY_SCORE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-grid-consistency-score',
            'title' => __('Grid Consistency Score', 'wpshadow'),
            'description' => __('Scores variance of grid and gap usage across templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-grid-consistency-score',
            'training_link' => 'https://wpshadow.com/training/design-grid-consistency-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
