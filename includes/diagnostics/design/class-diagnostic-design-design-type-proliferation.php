<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Type Proliferation
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-type-proliferation
 * Training: https://wpshadow.com/training/design-type-proliferation
 */
class Diagnostic_Design_DESIGN_TYPE_PROLIFERATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-type-proliferation',
            'title' => __('Type Proliferation', 'wpshadow'),
            'description' => __('Counts unique font sizes and line-heights; flags off-scale usage.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-type-proliferation',
            'training_link' => 'https://wpshadow.com/training/design-type-proliferation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
