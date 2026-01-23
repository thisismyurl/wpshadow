<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Accent Color Hierarchy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-accent-color-hierarchy
 * Training: https://wpshadow.com/training/design-accent-color-hierarchy
 */
class Diagnostic_Design_ACCENT_COLOR_HIERARCHY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-accent-color-hierarchy',
            'title' => __('Accent Color Hierarchy', 'wpshadow'),
            'description' => __('Confirms secondary colors used sparingly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-accent-color-hierarchy',
            'training_link' => 'https://wpshadow.com/training/design-accent-color-hierarchy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}