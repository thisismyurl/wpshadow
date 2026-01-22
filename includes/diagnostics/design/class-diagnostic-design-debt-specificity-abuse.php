<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS Specificity Abuse
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-specificity-abuse
 * Training: https://wpshadow.com/training/design-debt-specificity-abuse
 */
class Diagnostic_Design_DEBT_SPECIFICITY_ABUSE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-specificity-abuse',
            'title' => __('CSS Specificity Abuse', 'wpshadow'),
            'description' => __('Detects overly specific selectors, !important overuse.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-specificity-abuse',
            'training_link' => 'https://wpshadow.com/training/design-debt-specificity-abuse',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
