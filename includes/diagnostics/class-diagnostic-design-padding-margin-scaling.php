<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Padding & Margin Scaling
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-padding-margin-scaling
 * Training: https://wpshadow.com/training/design-padding-margin-scaling
 */
class Diagnostic_Design_PADDING_MARGIN_SCALING {
    public static function check() {
        return [
            'id' => 'design-padding-margin-scaling',
            'title' => __('Padding & Margin Scaling', 'wpshadow'),
            'description' => __('Verifies margins/padding scale with viewport (clamp or breakpoints).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-padding-margin-scaling',
            'training_link' => 'https://wpshadow.com/training/design-padding-margin-scaling',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
