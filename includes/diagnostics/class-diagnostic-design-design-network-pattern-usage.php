<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Network Pattern Usage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-pattern-usage
 * Training: https://wpshadow.com/training/design-network-pattern-usage
 */
class Diagnostic_Design_DESIGN_NETWORK_PATTERN_USAGE {
    public static function check() {
        return [
            'id' => 'design-network-pattern-usage',
            'title' => __('Network Pattern Usage', 'wpshadow'),
            'description' => __('Measures pattern usage across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-pattern-usage',
            'training_link' => 'https://wpshadow.com/training/design-network-pattern-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

