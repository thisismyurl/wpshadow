<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Iconography Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-iconography-drift
 * Training: https://wpshadow.com/training/design-vrt-iconography-drift
 */
class Diagnostic_Design_DESIGN_VRT_ICONOGRAPHY_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-iconography-drift',
            'title' => __('VRT Iconography Drift', 'wpshadow'),
            'description' => __('Detects icon set, size, or stroke changes versus baseline.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-iconography-drift',
            'training_link' => 'https://wpshadow.com/training/design-vrt-iconography-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
