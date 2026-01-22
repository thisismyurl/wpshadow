<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Pattern Reuse Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-pattern-reuse-score
 * Training: https://wpshadow.com/training/design-pattern-reuse-score
 */
class Diagnostic_Design_DESIGN_PATTERN_REUSE_SCORE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-pattern-reuse-score',
            'title' => __('Pattern Reuse Score', 'wpshadow'),
            'description' => __('Measures percentage of pages using patterns versus custom one-offs.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-pattern-reuse-score',
            'training_link' => 'https://wpshadow.com/training/design-pattern-reuse-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
