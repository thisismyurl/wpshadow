<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unused Component Variants
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-unused-variants
 * Training: https://wpshadow.com/training/design-debt-unused-variants
 */
class Diagnostic_Design_DEBT_UNUSED_VARIANTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-unused-variants',
            'title' => __('Unused Component Variants', 'wpshadow'),
            'description' => __('Detects defined component variants never used.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-unused-variants',
            'training_link' => 'https://wpshadow.com/training/design-debt-unused-variants',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}