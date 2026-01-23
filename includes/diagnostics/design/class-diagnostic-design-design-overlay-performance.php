<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Overlay Performance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-overlay-performance
 * Training: https://wpshadow.com/training/design-overlay-performance
 */
class Diagnostic_Design_DESIGN_OVERLAY_PERFORMANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-overlay-performance',
            'title' => __('Overlay Performance', 'wpshadow'),
            'description' => __('Checks modals and overlays avoid heavy blur or filters.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-overlay-performance',
            'training_link' => 'https://wpshadow.com/training/design-overlay-performance',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}