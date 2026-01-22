<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Button Style Controls
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-button-style-controls
 * Training: https://wpshadow.com/training/design-button-style-controls
 */
class Diagnostic_Design_DESIGN_BUTTON_STYLE_CONTROLS {
    public static function check() {
        return [
            'id' => 'design-button-style-controls',
            'title' => __('Button Style Controls', 'wpshadow'),
            'description' => __('Checks button controls map to component styles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-style-controls',
            'training_link' => 'https://wpshadow.com/training/design-button-style-controls',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

