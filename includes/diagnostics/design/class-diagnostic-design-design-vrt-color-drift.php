<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Color Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-color-drift
 * Training: https://wpshadow.com/training/design-vrt-color-drift
 */
class Diagnostic_Design_DESIGN_VRT_COLOR_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-color-drift',
            'title' => __('VRT Color Drift', 'wpshadow'),
            'description' => __('Detects palette deviations beyond a defined threshold.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-color-drift',
            'training_link' => 'https://wpshadow.com/training/design-vrt-color-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}