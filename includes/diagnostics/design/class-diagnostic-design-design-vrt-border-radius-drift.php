<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Border Radius Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-border-radius-drift
 * Training: https://wpshadow.com/training/design-vrt-border-radius-drift
 */
class Diagnostic_Design_DESIGN_VRT_BORDER_RADIUS_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-border-radius-drift',
            'title' => __('VRT Border Radius Drift', 'wpshadow'),
            'description' => __('Detects radius changes beyond the approved scale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-border-radius-drift',
            'training_link' => 'https://wpshadow.com/training/design-vrt-border-radius-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}