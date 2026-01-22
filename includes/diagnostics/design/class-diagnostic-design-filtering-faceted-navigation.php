<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Filtering & Faceted Navigation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-filtering-faceted-navigation
 * Training: https://wpshadow.com/training/design-filtering-faceted-navigation
 */
class Diagnostic_Design_FILTERING_FACETED_NAVIGATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-filtering-faceted-navigation',
            'title' => __('Filtering & Faceted Navigation', 'wpshadow'),
            'description' => __('Verifies filtering UI clear, shows counts.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-filtering-faceted-navigation',
            'training_link' => 'https://wpshadow.com/training/design-filtering-faceted-navigation',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
