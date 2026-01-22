<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unsanitized Input Detection
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-unsanitized-input
 * Training: https://wpshadow.com/training/code-security-unsanitized-input
 */
class Diagnostic_Code_CODE_SECURITY_UNSANITIZED_INPUT {
    public static function check() {
        return [
            'id' => 'code-security-unsanitized-input',
            'title' => __('Unsanitized Input Detection', 'wpshadow'),
            'description' => __('Detects GET/POST/REQUEST/COOKIE/FILES data used without sanitization.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-unsanitized-input',
            'training_link' => 'https://wpshadow.com/training/code-security-unsanitized-input',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

