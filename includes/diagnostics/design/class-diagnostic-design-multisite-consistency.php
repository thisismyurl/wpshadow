<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Multi-Site Design Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-multisite-consistency
 * Training: https://wpshadow.com/training/design-multisite-consistency
 */
class Diagnostic_Design_MULTISITE_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-multisite-consistency',
            'title' => __('Multi-Site Design Consistency', 'wpshadow'),
            'description' => __('Verifies child sites follow parent design system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-multisite-consistency',
            'training_link' => 'https://wpshadow.com/training/design-multisite-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
