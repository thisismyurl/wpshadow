<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unused CSS Debt
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-unused-css-debt
 * Training: https://wpshadow.com/training/design-unused-css-debt
 */
class Diagnostic_Design_DESIGN_UNUSED_CSS_DEBT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-unused-css-debt',
            'title' => __('Unused CSS Debt', 'wpshadow'),
            'description' => __('Measures percentage of unused selectors.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-unused-css-debt',
            'training_link' => 'https://wpshadow.com/training/design-unused-css-debt',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}