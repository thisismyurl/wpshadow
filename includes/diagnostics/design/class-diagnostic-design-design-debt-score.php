<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Design Debt Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-score
 * Training: https://wpshadow.com/training/design-debt-score
 */
class Diagnostic_Design_DESIGN_DEBT_SCORE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-score',
            'title' => __('Design Debt Score', 'wpshadow'),
            'description' => __('Composite design debt score across color, type, spacing, and specificity.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-score',
            'training_link' => 'https://wpshadow.com/training/design-debt-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
