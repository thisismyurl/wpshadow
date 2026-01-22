<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Design Debt Trend
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-trend
 * Training: https://wpshadow.com/training/design-debt-trend
 */
class Diagnostic_Design_DESIGN_DEBT_TREND {
    public static function check() {
        return [
            'id' => 'design-debt-trend',
            'title' => __('Design Debt Trend', 'wpshadow'),
            'description' => __('Tracks design debt score over time.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-trend',
            'training_link' => 'https://wpshadow.com/training/design-debt-trend',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

