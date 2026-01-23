<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Column Spacing on Mobile
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-column-spacing-mobile
 * Training: https://wpshadow.com/training/design-column-spacing-mobile
 */
class Diagnostic_Design_COLUMN_SPACING_MOBILE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-column-spacing-mobile',
            'title' => __('Column Spacing on Mobile', 'wpshadow'),
            'description' => __('Verifies multi-column layouts stack to single column on mobile.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-column-spacing-mobile',
            'training_link' => 'https://wpshadow.com/training/design-column-spacing-mobile',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}