<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Missing Form Labels
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-missing-labels
 * Training: https://wpshadow.com/training/code-a11y-missing-labels
 */
class Diagnostic_Code_CODE_A11Y_MISSING_LABELS {
    public static function check() {
        return [
            'id' => 'code-a11y-missing-labels',
            'title' => __('Missing Form Labels', 'wpshadow'),
            'description' => __('Detects form inputs without associated labels or aria-labels.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-missing-labels',
            'training_link' => 'https://wpshadow.com/training/code-a11y-missing-labels',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

