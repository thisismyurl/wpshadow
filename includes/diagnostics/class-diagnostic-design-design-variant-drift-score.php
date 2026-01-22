<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Variant Drift Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-variant-drift-score
 * Training: https://wpshadow.com/training/design-variant-drift-score
 */
class Diagnostic_Design_DESIGN_VARIANT_DRIFT_SCORE {
    public static function check() {
        return [
            'id' => 'design-variant-drift-score',
            'title' => __('Variant Drift Score', 'wpshadow'),
            'description' => __('Measures deviation of variants from canonical tokens.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-variant-drift-score',
            'training_link' => 'https://wpshadow.com/training/design-variant-drift-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

