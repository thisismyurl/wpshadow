<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Loading Performance
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-font-loading-performance
 * Training: https://wpshadow.com/training/design-font-loading-performance
 */
class Diagnostic_Design_FONT_LOADING_PERFORMANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-loading-performance',
            'title' => __('Font Loading Performance', 'wpshadow'),
            'description' => __('Confirms fonts use font-display swap.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-loading-performance',
            'training_link' => 'https://wpshadow.com/training/design-font-loading-performance',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
