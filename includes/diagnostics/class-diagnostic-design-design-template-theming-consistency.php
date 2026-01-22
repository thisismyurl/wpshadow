<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Template Theming Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-template-theming-consistency
 * Training: https://wpshadow.com/training/design-template-theming-consistency
 */
class Diagnostic_Design_DESIGN_TEMPLATE_THEMING_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-template-theming-consistency',
            'title' => __('Template Theming Consistency', 'wpshadow'),
            'description' => __('Compares header/footer/sidebar patterns for token drift across templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-template-theming-consistency',
            'training_link' => 'https://wpshadow.com/training/design-template-theming-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

