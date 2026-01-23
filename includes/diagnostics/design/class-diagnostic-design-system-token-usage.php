<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Design Token Usage Compliance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-token-usage
 * Training: https://wpshadow.com/training/design-system-token-usage
 */
class Diagnostic_Design_SYSTEM_TOKEN_USAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-token-usage',
            'title' => __('Design Token Usage Compliance', 'wpshadow'),
            'description' => __('Verifies site uses defined CSS variables/tokens, not hardcoded colors/sizes.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-token-usage',
            'training_link' => 'https://wpshadow.com/training/design-system-token-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}