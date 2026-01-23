<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Accessible Color Tokens
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-accessible-tokens
 * Training: https://wpshadow.com/training/design-system-accessible-tokens
 */
class Diagnostic_Design_SYSTEM_ACCESSIBLE_TOKENS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-accessible-tokens',
            'title' => __('Accessible Color Tokens', 'wpshadow'),
            'description' => __('Verifies system token colors meet WCAG contrast requirements.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-accessible-tokens',
            'training_link' => 'https://wpshadow.com/training/design-system-accessible-tokens',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}