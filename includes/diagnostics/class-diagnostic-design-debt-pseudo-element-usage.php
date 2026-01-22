<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Pseudo-Element Abuse
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-pseudo-element-usage
 * Training: https://wpshadow.com/training/design-debt-pseudo-element-usage
 */
class Diagnostic_Design_DEBT_PSEUDO_ELEMENT_USAGE {
    public static function check() {
        return [
            'id' => 'design-debt-pseudo-element-usage',
            'title' => __('Pseudo-Element Abuse', 'wpshadow'),
            'description' => __('Detects excessive ::before/::after (performance).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-pseudo-element-usage',
            'training_link' => 'https://wpshadow.com/training/design-debt-pseudo-element-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
