<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Shadow & Elevation System
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-shadow-elevation-system
 * Training: https://wpshadow.com/training/design-shadow-elevation-system
 */
class Diagnostic_Design_SHADOW_ELEVATION_SYSTEM extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-shadow-elevation-system',
            'title' => __('Shadow & Elevation System', 'wpshadow'),
            'description' => __('Checks if shadows follow elevation scale (z-index levels with consistent shadow definitions).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-shadow-elevation-system',
            'training_link' => 'https://wpshadow.com/training/design-shadow-elevation-system',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
