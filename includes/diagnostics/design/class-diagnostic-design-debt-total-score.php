<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Total Design Debt Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-total-score
 * Training: https://wpshadow.com/training/design-debt-total-score
 */
class Diagnostic_Design_DEBT_TOTAL_SCORE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-total-score',
            'title' => __('Total Design Debt Score', 'wpshadow'),
            'description' => __('Composite debt score (1-100 scale).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-total-score',
            'training_link' => 'https://wpshadow.com/training/design-debt-total-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}