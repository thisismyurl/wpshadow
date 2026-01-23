<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Shadow/Elevation Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-shadow-elevation
 * Training: https://wpshadow.com/training/design-system-shadow-elevation
 */
class Diagnostic_Design_SYSTEM_SHADOW_ELEVATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-shadow-elevation',
            'title' => __('Shadow/Elevation Enforcement', 'wpshadow'),
            'description' => __('Verifies shadows use defined elevation scale (z0, z1, z2, etc.).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-shadow-elevation',
            'training_link' => 'https://wpshadow.com/training/design-system-shadow-elevation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}