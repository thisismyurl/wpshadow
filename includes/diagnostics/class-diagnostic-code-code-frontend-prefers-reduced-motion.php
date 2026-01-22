<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Prefers Reduced Motion
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-prefers-reduced-motion
 * Training: https://wpshadow.com/training/code-frontend-prefers-reduced-motion
 */
class Diagnostic_Code_CODE_FRONTEND_PREFERS_REDUCED_MOTION {
    public static function check() {
        return [
            'id' => 'code-frontend-prefers-reduced-motion',
            'title' => __('Prefers Reduced Motion', 'wpshadow'),
            'description' => __('Detects animations not respecting user accessibility preference.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-prefers-reduced-motion',
            'training_link' => 'https://wpshadow.com/training/code-frontend-prefers-reduced-motion',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

