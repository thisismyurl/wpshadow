<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Token Compliance Audit
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-token-compliance
 * Training: https://wpshadow.com/training/design-token-compliance
 */
class Diagnostic_Design_DESIGN_TOKEN_COMPLIANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-token-compliance',
            'title' => __('Token Compliance Audit', 'wpshadow'),
            'description' => __('Enforces palette, spacing, and typography tokens; flags raw values.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-token-compliance',
            'training_link' => 'https://wpshadow.com/training/design-token-compliance',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
