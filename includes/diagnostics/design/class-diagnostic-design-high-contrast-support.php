<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: High Contrast Mode Support
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-high-contrast-support
 * Training: https://wpshadow.com/training/design-high-contrast-support
 */
class Diagnostic_Design_HIGH_CONTRAST_SUPPORT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-high-contrast-support',
            'title' => __('High Contrast Mode Support', 'wpshadow'),
            'description' => __('Tests with Windows high contrast mode.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-high-contrast-support',
            'training_link' => 'https://wpshadow.com/training/design-high-contrast-support',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}