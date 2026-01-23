<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Shadow Drift
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-shadow-drift
 * Training: https://wpshadow.com/training/design-vrt-shadow-drift
 */
class Diagnostic_Design_DESIGN_VRT_SHADOW_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-shadow-drift',
            'title' => __('VRT Shadow Drift', 'wpshadow'),
            'description' => __('Detects shadow changes versus token scales.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-shadow-drift',
            'training_link' => 'https://wpshadow.com/training/design-vrt-shadow-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}