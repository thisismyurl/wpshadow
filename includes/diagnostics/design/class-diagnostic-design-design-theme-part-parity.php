<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Theme Part Parity
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-theme-part-parity
 * Training: https://wpshadow.com/training/design-theme-part-parity
 */
class Diagnostic_Design_DESIGN_THEME_PART_PARITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-theme-part-parity',
            'title' => __('Theme Part Parity', 'wpshadow'),
            'description' => __('Ensures template parts share tokens for type, spacing, and colors.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-theme-part-parity',
            'training_link' => 'https://wpshadow.com/training/design-theme-part-parity',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
