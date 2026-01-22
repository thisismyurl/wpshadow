<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: 3D Transform Fallback
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-transform-3d-fallback
 * Training: https://wpshadow.com/training/design-transform-3d-fallback
 */
class Diagnostic_Design_TRANSFORM_3D_FALLBACK {
    public static function check() {
        return [
            'id' => 'design-transform-3d-fallback',
            'title' => __('3D Transform Fallback', 'wpshadow'),
            'description' => __('Validates 3D transforms degrade gracefully.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-transform-3d-fallback',
            'training_link' => 'https://wpshadow.com/training/design-transform-3d-fallback',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
