<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Breadcrumb Accuracy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-breadcrumb-accuracy
 * Training: https://wpshadow.com/training/design-breadcrumb-accuracy
 */
class Diagnostic_Design_BREADCRUMB_ACCURACY {
    public static function check() {
        return [
            'id' => 'design-breadcrumb-accuracy',
            'title' => __('Breadcrumb Accuracy', 'wpshadow'),
            'description' => __('Verifies breadcrumbs accurately reflect hierarchy.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-breadcrumb-accuracy',
            'training_link' => 'https://wpshadow.com/training/design-breadcrumb-accuracy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
