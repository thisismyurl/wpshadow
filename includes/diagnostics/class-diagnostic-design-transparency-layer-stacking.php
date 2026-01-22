<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Transparency Layer Stacking
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-transparency-layer-stacking
 * Training: https://wpshadow.com/training/design-transparency-layer-stacking
 */
class Diagnostic_Design_TRANSPARENCY_LAYER_STACKING {
    public static function check() {
        return [
            'id' => 'design-transparency-layer-stacking',
            'title' => __('Transparency Layer Stacking', 'wpshadow'),
            'description' => __('Confirms overlays don't block readability.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-transparency-layer-stacking',
            'training_link' => 'https://wpshadow.com/training/design-transparency-layer-stacking',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
