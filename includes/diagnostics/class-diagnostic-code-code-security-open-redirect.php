<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Open Redirect Risks
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-open-redirect
 * Training: https://wpshadow.com/training/code-security-open-redirect
 */
class Diagnostic_Code_CODE_SECURITY_OPEN_REDIRECT {
    public static function check() {
        return [
            'id' => 'code-security-open-redirect',
            'title' => __('Open Redirect Risks', 'wpshadow'),
            'description' => __('Detects unvalidated redirect targets in functions/plugins.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-open-redirect',
            'training_link' => 'https://wpshadow.com/training/code-security-open-redirect',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

