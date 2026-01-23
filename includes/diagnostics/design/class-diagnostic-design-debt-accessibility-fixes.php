<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Accessibility Debt Ratio
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-accessibility-fixes
 * Training: https://wpshadow.com/training/design-debt-accessibility-fixes
 */
class Diagnostic_Design_DEBT_ACCESSIBILITY_FIXES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-accessibility-fixes',
            'title' => __('Accessibility Debt Ratio', 'wpshadow'),
            'description' => __('Counts a11y issues fixed vs still pending.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-accessibility-fixes',
            'training_link' => 'https://wpshadow.com/training/design-debt-accessibility-fixes',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}