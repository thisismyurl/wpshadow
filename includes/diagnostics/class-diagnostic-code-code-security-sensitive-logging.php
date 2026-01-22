<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Sensitive Data Logged
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-sensitive-logging
 * Training: https://wpshadow.com/training/code-security-sensitive-logging
 */
class Diagnostic_Code_CODE_SECURITY_SENSITIVE_LOGGING {
    public static function check() {
        return [
            'id' => 'code-security-sensitive-logging',
            'title' => __('Sensitive Data Logged', 'wpshadow'),
            'description' => __('Detects tokens, keys, or PII in error logs.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-sensitive-logging',
            'training_link' => 'https://wpshadow.com/training/code-security-sensitive-logging',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

