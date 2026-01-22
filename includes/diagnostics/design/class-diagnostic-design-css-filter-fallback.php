<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS Filter Fallback
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-filter-fallback
 * Training: https://wpshadow.com/training/design-css-filter-fallback
 */
class Diagnostic_Design_CSS_FILTER_FALLBACK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-filter-fallback',
            'title' => __('CSS Filter Fallback', 'wpshadow'),
            'description' => __('Checks CSS filters have fallback.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-filter-fallback',
            'training_link' => 'https://wpshadow.com/training/design-css-filter-fallback',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
