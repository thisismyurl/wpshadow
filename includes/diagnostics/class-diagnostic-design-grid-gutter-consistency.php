<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Grid Gutter Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-grid-gutter-consistency
 * Training: https://wpshadow.com/training/design-grid-gutter-consistency
 */
class Diagnostic_Design_GRID_GUTTER_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-grid-gutter-consistency',
            'title' => __('Grid Gutter Consistency', 'wpshadow'),
            'description' => __('Confirms CSS Grid uses consistent gutter sizes (16px-32px).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-grid-gutter-consistency',
            'training_link' => 'https://wpshadow.com/training/design-grid-gutter-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
