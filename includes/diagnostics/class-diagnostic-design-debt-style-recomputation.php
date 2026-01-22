<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Style Recomputation Frequency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-style-recomputation
 * Training: https://wpshadow.com/training/design-debt-style-recomputation
 */
class Diagnostic_Design_DEBT_STYLE_RECOMPUTATION {
    public static function check() {
        return [
            'id' => 'design-debt-style-recomputation',
            'title' => __('Style Recomputation Frequency', 'wpshadow'),
            'description' => __('Measures how often DOM requires style recalculation.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-style-recomputation',
            'training_link' => 'https://wpshadow.com/training/design-debt-style-recomputation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
