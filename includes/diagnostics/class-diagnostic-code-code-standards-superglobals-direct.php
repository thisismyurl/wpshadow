<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Direct Superglobal Access
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-superglobals-direct
 * Training: https://wpshadow.com/training/code-standards-superglobals-direct
 */
class Diagnostic_Code_CODE_STANDARDS_SUPERGLOBALS_DIRECT {
    public static function check() {
        return [
            'id' => 'code-standards-superglobals-direct',
            'title' => __('Direct Superglobal Access', 'wpshadow'),
            'description' => __('Flags direct $_GET/$_POST instead of sanitized wrappers.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-superglobals-direct',
            'training_link' => 'https://wpshadow.com/training/code-standards-superglobals-direct',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

