<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Text Size Responsive Scaling
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-text-size-responsive-scaling
 * Training: https://wpshadow.com/training/design-text-size-responsive-scaling
 */
class Diagnostic_Design_TEXT_SIZE_RESPONSIVE_SCALING {
    public static function check() {
        return [
            'id' => 'design-text-size-responsive-scaling',
            'title' => __('Text Size Responsive Scaling', 'wpshadow'),
            'description' => __('Checks body text size responsive, uses fluid typography.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-text-size-responsive-scaling',
            'training_link' => 'https://wpshadow.com/training/design-text-size-responsive-scaling',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
