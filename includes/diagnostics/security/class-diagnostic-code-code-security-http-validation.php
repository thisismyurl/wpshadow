<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: HTTP/HTTPS Validation
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-http-validation
 * Training: https://wpshadow.com/training/code-security-http-validation
 */
class Diagnostic_Code_CODE_SECURITY_HTTP_VALIDATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-security-http-validation',
            'title' => __('HTTP/HTTPS Validation', 'wpshadow'),
            'description' => __('Detects insecure HTTP API calls where HTTPS required.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-http-validation',
            'training_link' => 'https://wpshadow.com/training/code-security-http-validation',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
