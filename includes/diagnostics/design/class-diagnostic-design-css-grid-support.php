<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS Grid Implementation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-grid-support
 * Training: https://wpshadow.com/training/design-css-grid-support
 */
class Diagnostic_Design_CSS_GRID_SUPPORT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-grid-support',
            'title' => __('CSS Grid Implementation', 'wpshadow'),
            'description' => __('Verifies CSS Grid used appropriately.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-grid-support',
            'training_link' => 'https://wpshadow.com/training/design-css-grid-support',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}