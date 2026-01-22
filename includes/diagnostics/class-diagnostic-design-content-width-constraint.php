<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Content Width Constraint
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-content-width-constraint
 * Training: https://wpshadow.com/training/design-content-width-constraint
 */
class Diagnostic_Design_CONTENT_WIDTH_CONSTRAINT {
    public static function check() {
        return [
            'id' => 'design-content-width-constraint',
            'title' => __('Content Width Constraint', 'wpshadow'),
            'description' => __('Checks content width constrained (max-width).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-content-width-constraint',
            'training_link' => 'https://wpshadow.com/training/design-content-width-constraint',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
