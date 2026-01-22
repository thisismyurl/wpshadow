<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Utility Class Ratio
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-utility-class-ratio
 * Training: https://wpshadow.com/training/design-debt-utility-class-ratio
 */
class Diagnostic_Design_DEBT_UTILITY_CLASS_RATIO {
    public static function check() {
        return [
            'id' => 'design-debt-utility-class-ratio',
            'title' => __('Utility Class Ratio', 'wpshadow'),
            'description' => __('Measures utility class usage (Tailwind vs BEM).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-utility-class-ratio',
            'training_link' => 'https://wpshadow.com/training/design-debt-utility-class-ratio',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
