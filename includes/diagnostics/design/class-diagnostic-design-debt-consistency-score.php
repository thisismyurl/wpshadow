<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Design Consistency Debt Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-consistency-score
 * Training: https://wpshadow.com/training/design-debt-consistency-score
 */
class Diagnostic_Design_DEBT_CONSISTENCY_SCORE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-consistency-score',
            'title' => __('Design Consistency Debt Score', 'wpshadow'),
            'description' => __('Overall inconsistency score vs design system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-consistency-score',
            'training_link' => 'https://wpshadow.com/training/design-debt-consistency-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
