<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Print Stylesheet Optimization
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-print-stylesheet-optimization
 * Training: https://wpshadow.com/training/design-print-stylesheet-optimization
 */
class Diagnostic_Design_PRINT_STYLESHEET_OPTIMIZATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-print-stylesheet-optimization',
            'title' => __('Print Stylesheet Optimization', 'wpshadow'),
            'description' => __('Confirms print CSS hides nav, ads, sidebars.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-print-stylesheet-optimization',
            'training_link' => 'https://wpshadow.com/training/design-print-stylesheet-optimization',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
