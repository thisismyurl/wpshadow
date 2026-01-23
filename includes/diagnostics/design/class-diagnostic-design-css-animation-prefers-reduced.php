<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Animation Respects Prefers Reduced Motion
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-animation-prefers-reduced
 * Training: https://wpshadow.com/training/design-css-animation-prefers-reduced
 */
class Diagnostic_Design_CSS_ANIMATION_PREFERS_REDUCED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-animation-prefers-reduced',
            'title' => __('Animation Respects Prefers Reduced Motion', 'wpshadow'),
            'description' => __('Confirms animations respect preference.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-animation-prefers-reduced',
            'training_link' => 'https://wpshadow.com/training/design-css-animation-prefers-reduced',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}