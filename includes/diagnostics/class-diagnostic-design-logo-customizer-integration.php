<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Logo Customizer Integration
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-logo-customizer-integration
 * Training: https://wpshadow.com/training/design-logo-customizer-integration
 */
class Diagnostic_Design_LOGO_CUSTOMIZER_INTEGRATION {
    public static function check() {
        return [
            'id' => 'design-logo-customizer-integration',
            'title' => __('Logo Customizer Integration', 'wpshadow'),
            'description' => __('Checks if custom logo properly sized/positioned per design.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-logo-customizer-integration',
            'training_link' => 'https://wpshadow.com/training/design-logo-customizer-integration',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
