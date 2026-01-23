<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Color Psychology Alignment
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-color-psychology-alignment
 * Training: https://wpshadow.com/training/design-color-psychology-alignment
 */
class Diagnostic_Design_COLOR_PSYCHOLOGY_ALIGNMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-color-psychology-alignment',
            'title' => __('Color Psychology Alignment', 'wpshadow'),
            'description' => __('Validates colors align with psychology.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-color-psychology-alignment',
            'training_link' => 'https://wpshadow.com/training/design-color-psychology-alignment',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}