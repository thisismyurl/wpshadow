<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Layout Thrashing Detection
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-layout-thrashing
 * Training: https://wpshadow.com/training/design-debt-layout-thrashing
 */
class Diagnostic_Design_DEBT_LAYOUT_THRASHING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-layout-thrashing',
            'title' => __('Layout Thrashing Detection', 'wpshadow'),
            'description' => __('Detects read/write DOM operations in loops.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-layout-thrashing',
            'training_link' => 'https://wpshadow.com/training/design-debt-layout-thrashing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
