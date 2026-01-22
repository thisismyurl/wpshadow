<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Shortcode Unsafe Output
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-shortcode-unsafe
 * Training: https://wpshadow.com/training/code-shortcode-unsafe
 */
class Diagnostic_Code_CODE_SHORTCODE_UNSAFE {
    public static function check() {
        return [
            'id' => 'code-shortcode-unsafe',
            'title' => __('Shortcode Unsafe Output', 'wpshadow'),
            'description' => __('Detects shortcode output without escaping or validation.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-shortcode-unsafe',
            'training_link' => 'https://wpshadow.com/training/code-shortcode-unsafe',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

