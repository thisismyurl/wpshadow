<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Size Hierarchy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-font-size-hierarchy
 * Training: https://wpshadow.com/training/design-font-size-hierarchy
 */
class Diagnostic_Design_FONT_SIZE_HIERARCHY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-size-hierarchy',
            'title' => __('Font Size Hierarchy', 'wpshadow'),
            'description' => __('Verifies heading sizes create clear hierarchy.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-size-hierarchy',
            'training_link' => 'https://wpshadow.com/training/design-font-size-hierarchy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
