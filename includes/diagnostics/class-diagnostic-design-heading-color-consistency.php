<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Heading Color Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-heading-color-consistency
 * Training: https://wpshadow.com/training/design-heading-color-consistency
 */
class Diagnostic_Design_HEADING_COLOR_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-heading-color-consistency',
            'title' => __('Heading Color Consistency', 'wpshadow'),
            'description' => __('Confirms heading colors consistent.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-heading-color-consistency',
            'training_link' => 'https://wpshadow.com/training/design-heading-color-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
