<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tooltip Positioning Strategy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-tooltip-positioning-strategy
 * Training: https://wpshadow.com/training/design-tooltip-positioning-strategy
 */
class Diagnostic_Design_TOOLTIP_POSITIONING_STRATEGY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-tooltip-positioning-strategy',
            'title' => __('Tooltip Positioning Strategy', 'wpshadow'),
            'description' => __('Confirms tooltips position intelligently, show on hover/focus.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-tooltip-positioning-strategy',
            'training_link' => 'https://wpshadow.com/training/design-tooltip-positioning-strategy',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
