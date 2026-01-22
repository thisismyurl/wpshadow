<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Breadcrumb Navigation Hierarchy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-breadcrumb-hierarchy
 * Training: https://wpshadow.com/training/design-breadcrumb-hierarchy
 */
class Diagnostic_Design_BREADCRUMB_HIERARCHY {
    public static function check() {
        return [
            'id' => 'design-breadcrumb-hierarchy',
            'title' => __('Breadcrumb Navigation Hierarchy', 'wpshadow'),
            'description' => __('Validates breadcrumb structure, separator visibility, active state clarity.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-breadcrumb-hierarchy',
            'training_link' => 'https://wpshadow.com/training/design-breadcrumb-hierarchy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
