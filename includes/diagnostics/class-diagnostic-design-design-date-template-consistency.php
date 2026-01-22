<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Date Template Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-date-template-consistency
 * Training: https://wpshadow.com/training/design-date-template-consistency
 */
class Diagnostic_Design_DESIGN_DATE_TEMPLATE_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-date-template-consistency',
            'title' => __('Date Template Consistency', 'wpshadow'),
            'description' => __('Checks date archives are consistent with blog archives.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-date-template-consistency',
            'training_link' => 'https://wpshadow.com/training/design-date-template-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

