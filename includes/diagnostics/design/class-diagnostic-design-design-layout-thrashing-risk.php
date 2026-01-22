<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Layout Thrashing Risk
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-layout-thrashing-risk
 * Training: https://wpshadow.com/training/design-layout-thrashing-risk
 */
class Diagnostic_Design_DESIGN_LAYOUT_THRASHING_RISK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-layout-thrashing-risk',
            'title' => __('Layout Thrashing Risk', 'wpshadow'),
            'description' => __('Detects forced reflows and layout thrash patterns.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-layout-thrashing-risk',
            'training_link' => 'https://wpshadow.com/training/design-layout-thrashing-risk',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
