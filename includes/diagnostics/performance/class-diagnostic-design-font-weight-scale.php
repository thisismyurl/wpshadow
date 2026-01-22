<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Weight Scale
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-font-weight-scale
 * Training: https://wpshadow.com/training/design-font-weight-scale
 */
class Diagnostic_Design_FONT_WEIGHT_SCALE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-weight-scale',
            'title' => __('Font Weight Scale', 'wpshadow'),
            'description' => __('Validates only 2-3 weights per family used.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-weight-scale',
            'training_link' => 'https://wpshadow.com/training/design-font-weight-scale',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
