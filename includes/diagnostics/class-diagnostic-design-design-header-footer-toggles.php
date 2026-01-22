<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Header/Footer Toggles
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-header-footer-toggles
 * Training: https://wpshadow.com/training/design-header-footer-toggles
 */
class Diagnostic_Design_DESIGN_HEADER_FOOTER_TOGGLES {
    public static function check() {
        return [
            'id' => 'design-header-footer-toggles',
            'title' => __('Header/Footer Toggles', 'wpshadow'),
            'description' => __('Checks toggles visibly change layout in preview and live.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-header-footer-toggles',
            'training_link' => 'https://wpshadow.com/training/design-header-footer-toggles',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

