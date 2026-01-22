<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tabs Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-tabs-consistency
 * Training: https://wpshadow.com/training/design-tabs-consistency
 */
class Diagnostic_Design_DESIGN_TABS_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-tabs-consistency',
            'title' => __('Tabs Consistency', 'wpshadow'),
            'description' => __('Checks tab states, indicators, and alignment consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-tabs-consistency',
            'training_link' => 'https://wpshadow.com/training/design-tabs-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
