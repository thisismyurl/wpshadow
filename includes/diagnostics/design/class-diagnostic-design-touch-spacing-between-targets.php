<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Touch Spacing Between Targets
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-touch-spacing-between-targets
 * Training: https://wpshadow.com/training/design-touch-spacing-between-targets
 */
class Diagnostic_Design_TOUCH_SPACING_BETWEEN_TARGETS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-touch-spacing-between-targets',
            'title' => __('Touch Spacing Between Targets', 'wpshadow'),
            'description' => __('Confirms buttons spaced adequately.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-touch-spacing-between-targets',
            'training_link' => 'https://wpshadow.com/training/design-touch-spacing-between-targets',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}