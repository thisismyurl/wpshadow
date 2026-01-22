<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Desktop Layout Hierarchy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-desktop-layout-hierarchy
 * Training: https://wpshadow.com/training/design-desktop-layout-hierarchy
 */
class Diagnostic_Design_DESKTOP_LAYOUT_HIERARCHY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-desktop-layout-hierarchy',
            'title' => __('Desktop Layout Hierarchy', 'wpshadow'),
            'description' => __('Confirms desktop layout uses whitespace, clear zones, optimal line length.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-desktop-layout-hierarchy',
            'training_link' => 'https://wpshadow.com/training/design-desktop-layout-hierarchy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
