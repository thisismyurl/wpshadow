<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unescaped Output Detection
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-unescaped-output
 * Training: https://wpshadow.com/training/code-security-unescaped-output
 */
class Diagnostic_Code_CODE_SECURITY_UNESCAPED_OUTPUT {
    public static function check() {
        return [
            'id' => 'code-security-unescaped-output',
            'title' => __('Unescaped Output Detection', 'wpshadow'),
            'description' => __('Detects HTML/attribute/URL output without escaping in templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-unescaped-output',
            'training_link' => 'https://wpshadow.com/training/code-security-unescaped-output',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

