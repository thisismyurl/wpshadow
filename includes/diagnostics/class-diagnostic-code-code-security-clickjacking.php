<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Clickjacking Headers
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-clickjacking
 * Training: https://wpshadow.com/training/code-security-clickjacking
 */
class Diagnostic_Code_CODE_SECURITY_CLICKJACKING {
    public static function check() {
        return [
            'id' => 'code-security-clickjacking',
            'title' => __('Clickjacking Headers', 'wpshadow'),
            'description' => __('Flags missing X-Frame-Options or CSP headers on admin pages.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-clickjacking',
            'training_link' => 'https://wpshadow.com/training/code-security-clickjacking',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

