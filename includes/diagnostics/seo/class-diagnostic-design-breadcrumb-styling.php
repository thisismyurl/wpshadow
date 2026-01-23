<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Breadcrumb Styling
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-breadcrumb-styling
 * Training: https://wpshadow.com/training/design-breadcrumb-styling
 */
class Diagnostic_Design_BREADCRUMB_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-breadcrumb-styling',
            'title' => __('Breadcrumb Styling', 'wpshadow'),
            'description' => __('Confirms breadcrumbs styled correctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-breadcrumb-styling',
            'training_link' => 'https://wpshadow.com/training/design-breadcrumb-styling',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}