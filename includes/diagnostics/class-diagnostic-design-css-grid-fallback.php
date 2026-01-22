<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: CSS Grid Fallback
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-grid-fallback
 * Training: https://wpshadow.com/training/design-css-grid-fallback
 */
class Diagnostic_Design_CSS_GRID_FALLBACK {
    public static function check() {
        return [
            'id' => 'design-css-grid-fallback',
            'title' => __('CSS Grid Fallback', 'wpshadow'),
            'description' => __('Confirms CSS Grid has fallback support.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-grid-fallback',
            'training_link' => 'https://wpshadow.com/training/design-css-grid-fallback',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
