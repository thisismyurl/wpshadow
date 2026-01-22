<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: RTL Text Direction
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-rtl-text-direction
 * Training: https://wpshadow.com/training/design-rtl-text-direction
 */
class Diagnostic_Design_RTL_TEXT_DIRECTION {
    public static function check() {
        return [
            'id' => 'design-rtl-text-direction',
            'title' => __('RTL Text Direction', 'wpshadow'),
            'description' => __('Validates text-direction set correctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-rtl-text-direction',
            'training_link' => 'https://wpshadow.com/training/design-rtl-text-direction',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
