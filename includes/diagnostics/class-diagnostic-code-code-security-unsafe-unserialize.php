<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unsafe Unserialize
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-unsafe-unserialize
 * Training: https://wpshadow.com/training/code-security-unsafe-unserialize
 */
class Diagnostic_Code_CODE_SECURITY_UNSAFE_UNSERIALIZE {
    public static function check() {
        return [
            'id' => 'code-security-unsafe-unserialize',
            'title' => __('Unsafe Unserialize', 'wpshadow'),
            'description' => __('Detects maybe_unserialize on untrusted data without guards.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-unsafe-unserialize',
            'training_link' => 'https://wpshadow.com/training/code-security-unsafe-unserialize',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

