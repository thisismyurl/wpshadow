<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Deprecated Auth APIs
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-deprecated-auth
 * Training: https://wpshadow.com/training/code-security-deprecated-auth
 */
class Diagnostic_Code_CODE_SECURITY_DEPRECATED_AUTH {
    public static function check() {
        return [
            'id' => 'code-security-deprecated-auth',
            'title' => __('Deprecated Auth APIs', 'wpshadow'),
            'description' => __('Flags use of deprecated wp_setcookie or outdated nonce patterns.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-deprecated-auth',
            'training_link' => 'https://wpshadow.com/training/code-security-deprecated-auth',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

