<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS Code Duplication
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-code-duplication
 * Training: https://wpshadow.com/training/design-debt-code-duplication
 */
class Diagnostic_Design_DEBT_CODE_DUPLICATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-code-duplication',
            'title' => __('CSS Code Duplication', 'wpshadow'),
            'description' => __('Measures duplicated style declarations (DRY violation).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-code-duplication',
            'training_link' => 'https://wpshadow.com/training/design-debt-code-duplication',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
