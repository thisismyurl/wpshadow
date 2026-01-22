<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Design Performance Debt Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-performance-score
 * Training: https://wpshadow.com/training/design-debt-performance-score
 */
class Diagnostic_Design_DEBT_PERFORMANCE_SCORE {
    public static function check() {
        return [
            'id' => 'design-debt-performance-score',
            'title' => __('Design Performance Debt Score', 'wpshadow'),
            'description' => __('Composite score of layout/paint/animation issues.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-performance-score',
            'training_link' => 'https://wpshadow.com/training/design-debt-performance-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
