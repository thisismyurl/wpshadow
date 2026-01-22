<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Customizer Preview Accuracy
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-customizer-preview-accurate
 * Training: https://wpshadow.com/training/design-customizer-preview-accurate
 */
class Diagnostic_Design_CUSTOMIZER_PREVIEW_ACCURATE {
    public static function check() {
        return [
            'id' => 'design-customizer-preview-accurate',
            'title' => __('Customizer Preview Accuracy', 'wpshadow'),
            'description' => __('Validates customizer preview matches live site.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-customizer-preview-accurate',
            'training_link' => 'https://wpshadow.com/training/design-customizer-preview-accurate',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
