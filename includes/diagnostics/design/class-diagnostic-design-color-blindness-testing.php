<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Color Blindness Simulation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-color-blindness-testing
 * Training: https://wpshadow.com/training/design-color-blindness-testing
 */
class Diagnostic_Design_COLOR_BLINDNESS_TESTING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-color-blindness-testing',
            'title' => __('Color Blindness Simulation', 'wpshadow'),
            'description' => __('Verifies design tested with color blindness simulators.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-color-blindness-testing',
            'training_link' => 'https://wpshadow.com/training/design-color-blindness-testing',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}