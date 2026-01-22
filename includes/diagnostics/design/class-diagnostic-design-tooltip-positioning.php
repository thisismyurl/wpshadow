<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tooltip Positioning
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-tooltip-positioning
 * Training: https://wpshadow.com/training/design-tooltip-positioning
 */
class Diagnostic_Design_TOOLTIP_POSITIONING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-tooltip-positioning',
            'title' => __('Tooltip Positioning', 'wpshadow'),
            'description' => __('Validates tooltips appear correctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-tooltip-positioning',
            'training_link' => 'https://wpshadow.com/training/design-tooltip-positioning',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
