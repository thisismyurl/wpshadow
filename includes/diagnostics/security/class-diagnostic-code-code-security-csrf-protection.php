<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSRF Protection Missing
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-csrf-protection
 * Training: https://wpshadow.com/training/code-security-csrf-protection
 */
class Diagnostic_Code_CODE_SECURITY_CSRF_PROTECTION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-security-csrf-protection',
            'title' => __('CSRF Protection Missing', 'wpshadow'),
            'description' => __('Detects forms lacking nonce fields for security.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-csrf-protection',
            'training_link' => 'https://wpshadow.com/training/code-security-csrf-protection',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
