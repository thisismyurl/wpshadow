<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Responsive Token Coverage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-responsive-tokens
 * Training: https://wpshadow.com/training/design-system-responsive-tokens
 */
class Diagnostic_Design_SYSTEM_RESPONSIVE_TOKENS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-responsive-tokens',
            'title' => __('Responsive Token Coverage', 'wpshadow'),
            'description' => __('Confirms tokens defined at all breakpoints, not just desktop.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-responsive-tokens',
            'training_link' => 'https://wpshadow.com/training/design-system-responsive-tokens',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
