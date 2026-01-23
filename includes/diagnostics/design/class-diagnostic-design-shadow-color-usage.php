<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Shadow Color Usage
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-shadow-color-usage
 * Training: https://wpshadow.com/training/design-shadow-color-usage
 */
class Diagnostic_Design_SHADOW_COLOR_USAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-shadow-color-usage',
            'title' => __('Shadow Color Usage', 'wpshadow'),
            'description' => __('Validates shadows use appropriate color.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-shadow-color-usage',
            'training_link' => 'https://wpshadow.com/training/design-shadow-color-usage',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}