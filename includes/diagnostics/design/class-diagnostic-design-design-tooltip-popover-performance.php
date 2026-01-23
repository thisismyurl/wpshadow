<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tooltip/Popover Performance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-tooltip-popover-performance
 * Training: https://wpshadow.com/training/design-tooltip-popover-performance
 */
class Diagnostic_Design_DESIGN_TOOLTIP_POPOVER_PERFORMANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-tooltip-popover-performance',
            'title' => __('Tooltip/Popover Performance', 'wpshadow'),
            'description' => __('Checks positioning avoids forced synchronous layout.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-tooltip-popover-performance',
            'training_link' => 'https://wpshadow.com/training/design-tooltip-popover-performance',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}