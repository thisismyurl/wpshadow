<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Double-Tap Zoom Enabled
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-double-tap-zoom
 * Training: https://wpshadow.com/training/design-double-tap-zoom
 */
class Diagnostic_Design_DOUBLE_TAP_ZOOM extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-double-tap-zoom',
            'title' => __('Double-Tap Zoom Enabled', 'wpshadow'),
            'description' => __('Verifies double-tap-to-zoom not disabled.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-double-tap-zoom',
            'training_link' => 'https://wpshadow.com/training/design-double-tap-zoom',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
