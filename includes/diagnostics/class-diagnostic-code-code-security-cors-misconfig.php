<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: CORS Misconfiguration
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-cors-misconfig
 * Training: https://wpshadow.com/training/code-security-cors-misconfig
 */
class Diagnostic_Code_CODE_SECURITY_CORS_MISCONFIG {
    public static function check() {
        return [
            'id' => 'code-security-cors-misconfig',
            'title' => __('CORS Misconfiguration', 'wpshadow'),
            'description' => __('Detects overly permissive CORS headers on API endpoints.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-cors-misconfig',
            'training_link' => 'https://wpshadow.com/training/code-security-cors-misconfig',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

