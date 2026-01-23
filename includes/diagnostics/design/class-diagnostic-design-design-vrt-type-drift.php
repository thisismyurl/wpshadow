<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Typography Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-type-drift
 * Training: https://wpshadow.com/training/design-vrt-type-drift
 */
class Diagnostic_Design_DESIGN_VRT_TYPE_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-type-drift',
            'title' => __('VRT Typography Drift', 'wpshadow'),
            'description' => __('Detects font, size, and line-height changes versus the type ramp.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-type-drift',
            'training_link' => 'https://wpshadow.com/training/design-vrt-type-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}