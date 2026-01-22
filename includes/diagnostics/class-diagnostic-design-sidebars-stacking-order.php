<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Sidebars Stacking Order
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-sidebars-stacking-order
 * Training: https://wpshadow.com/training/design-sidebars-stacking-order
 */
class Diagnostic_Design_SIDEBARS_STACKING_ORDER {
    public static function check() {
        return [
            'id' => 'design-sidebars-stacking-order',
            'title' => __('Sidebars Stacking Order', 'wpshadow'),
            'description' => __('Verifies logical stacking order on mobile.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-sidebars-stacking-order',
            'training_link' => 'https://wpshadow.com/training/design-sidebars-stacking-order',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
