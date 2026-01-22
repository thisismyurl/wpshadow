<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Inline Style Debt
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-inline-style-debt
 * Training: https://wpshadow.com/training/design-inline-style-debt
 */
class Diagnostic_Design_DESIGN_INLINE_STYLE_DEBT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-inline-style-debt',
            'title' => __('Inline Style Debt', 'wpshadow'),
            'description' => __('Measures inline style count and weight.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-inline-style-debt',
            'training_link' => 'https://wpshadow.com/training/design-inline-style-debt',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
