<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Legacy Code Ratio
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-legacy-code-ratio
 * Training: https://wpshadow.com/training/design-debt-legacy-code-ratio
 */
class Diagnostic_Design_DEBT_LEGACY_CODE_RATIO extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-legacy-code-ratio',
            'title' => __('Legacy Code Ratio', 'wpshadow'),
            'description' => __('Estimates % of code predating current design system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-legacy-code-ratio',
            'training_link' => 'https://wpshadow.com/training/design-debt-legacy-code-ratio',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}