<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hardcoded Inline Styles
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-hardcoded-inline-styles
 * Training: https://wpshadow.com/training/design-hardcoded-inline-styles
 */
class Diagnostic_Design_DESIGN_HARDCODED_INLINE_STYLES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-hardcoded-inline-styles',
            'title' => __('Hardcoded Inline Styles', 'wpshadow'),
            'description' => __('Flags inline styles where design tokens exist.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hardcoded-inline-styles',
            'training_link' => 'https://wpshadow.com/training/design-hardcoded-inline-styles',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
