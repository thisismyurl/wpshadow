<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Missing Skip Links
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-skip-links
 * Training: https://wpshadow.com/training/code-a11y-skip-links
 */
class Diagnostic_Code_CODE_A11Y_SKIP_LINKS {
    public static function check() {
        return [
            'id' => 'code-a11y-skip-links',
            'title' => __('Missing Skip Links', 'wpshadow'),
            'description' => __('Detects complex layouts without keyboard skip navigation.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-skip-links',
            'training_link' => 'https://wpshadow.com/training/code-a11y-skip-links',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

