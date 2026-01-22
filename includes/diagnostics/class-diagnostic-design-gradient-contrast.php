<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Gradient Contrast
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-gradient-contrast
 * Training: https://wpshadow.com/training/design-gradient-contrast
 */
class Diagnostic_Design_GRADIENT_CONTRAST {
    public static function check() {
        return [
            'id' => 'design-gradient-contrast',
            'title' => __('Gradient Contrast', 'wpshadow'),
            'description' => __('Confirms gradients maintain contrast.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-gradient-contrast',
            'training_link' => 'https://wpshadow.com/training/design-gradient-contrast',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
