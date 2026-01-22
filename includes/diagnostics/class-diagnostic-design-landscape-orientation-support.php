<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Landscape Orientation Full Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-landscape-orientation-support
 * Training: https://wpshadow.com/training/design-landscape-orientation-support
 */
class Diagnostic_Design_LANDSCAPE_ORIENTATION_SUPPORT {
    public static function check() {
        return [
            'id' => 'design-landscape-orientation-support',
            'title' => __('Landscape Orientation Full Support', 'wpshadow'),
            'description' => __('Verifies landscape support.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-landscape-orientation-support',
            'training_link' => 'https://wpshadow.com/training/design-landscape-orientation-support',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
