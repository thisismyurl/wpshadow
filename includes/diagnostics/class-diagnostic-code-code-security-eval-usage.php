<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unsafe eval/create_function
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-eval-usage
 * Training: https://wpshadow.com/training/code-security-eval-usage
 */
class Diagnostic_Code_CODE_SECURITY_EVAL_USAGE {
    public static function check() {
        return [
            'id' => 'code-security-eval-usage',
            'title' => __('Unsafe eval/create_function', 'wpshadow'),
            'description' => __('Flags use of eval(), create_function(), or preg_replace with e modifier.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-eval-usage',
            'training_link' => 'https://wpshadow.com/training/code-security-eval-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

