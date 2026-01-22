<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: REST Route Permission Checks
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-rest-permission
 * Training: https://wpshadow.com/training/code-security-rest-permission
 */
class Diagnostic_Code_CODE_SECURITY_REST_PERMISSION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-security-rest-permission',
            'title' => __('REST Route Permission Checks', 'wpshadow'),
            'description' => __('Detects REST endpoints lacking permission callbacks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-rest-permission',
            'training_link' => 'https://wpshadow.com/training/code-security-rest-permission',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
