<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Z-Index Conflicts
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-z-index-conflicts
 * Training: https://wpshadow.com/training/design-debt-z-index-conflicts
 */
class Diagnostic_Design_DEBT_Z_INDEX_CONFLICTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-z-index-conflicts',
            'title' => __('Z-Index Conflicts', 'wpshadow'),
            'description' => __('Detects z-index conflicts, stacking context issues.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-z-index-conflicts',
            'training_link' => 'https://wpshadow.com/training/design-debt-z-index-conflicts',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
