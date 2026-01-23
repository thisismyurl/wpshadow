<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Typography Responsive Scaling
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-typography-responsive
 * Training: https://wpshadow.com/training/design-typography-responsive
 */
class Diagnostic_Design_TYPOGRAPHY_RESPONSIVE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-typography-responsive',
            'title' => __('Typography Responsive Scaling', 'wpshadow'),
            'description' => __('Confirms font sizes scale at breakpoints.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-typography-responsive',
            'training_link' => 'https://wpshadow.com/training/design-typography-responsive',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}