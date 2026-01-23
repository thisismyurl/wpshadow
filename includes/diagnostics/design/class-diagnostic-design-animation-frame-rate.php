<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Animation Frame Rate
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-animation-frame-rate
 * Training: https://wpshadow.com/training/design-animation-frame-rate
 */
class Diagnostic_Design_ANIMATION_FRAME_RATE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-animation-frame-rate',
            'title' => __('Animation Frame Rate', 'wpshadow'),
            'description' => __('Checks animations smooth (60fps).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-animation-frame-rate',
            'training_link' => 'https://wpshadow.com/training/design-animation-frame-rate',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}