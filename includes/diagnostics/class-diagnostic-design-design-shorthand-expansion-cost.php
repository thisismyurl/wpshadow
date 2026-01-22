<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Shorthand Expansion Cost
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-shorthand-expansion-cost
 * Training: https://wpshadow.com/training/design-shorthand-expansion-cost
 */
class Diagnostic_Design_DESIGN_SHORTHAND_EXPANSION_COST {
    public static function check() {
        return [
            'id' => 'design-shorthand-expansion-cost',
            'title' => __('Shorthand Expansion Cost', 'wpshadow'),
            'description' => __('Estimates cost from overly verbose longhand or shorthand usage.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-shorthand-expansion-cost',
            'training_link' => 'https://wpshadow.com/training/design-shorthand-expansion-cost',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

