<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Nonce Enforcement on Admin Actions
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-nonce-enforcement
 * Training: https://wpshadow.com/training/code-security-nonce-enforcement
 */
class Diagnostic_Code_CODE_SECURITY_NONCE_ENFORCEMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-security-nonce-enforcement',
            'title' => __('Nonce Enforcement on Admin Actions', 'wpshadow'),
            'description' => __('Detects admin actions and AJAX handlers lacking nonce verification.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-nonce-enforcement',
            'training_link' => 'https://wpshadow.com/training/code-security-nonce-enforcement',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
