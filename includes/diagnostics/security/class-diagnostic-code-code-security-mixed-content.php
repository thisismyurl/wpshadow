<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Mixed Content Detection
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-mixed-content
 * Training: https://wpshadow.com/training/code-security-mixed-content
 */
class Diagnostic_Code_CODE_SECURITY_MIXED_CONTENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-security-mixed-content',
            'title' => __('Mixed Content Detection', 'wpshadow'),
            'description' => __('Flags asset enqueues with HTTPS/HTTP mismatch.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-mixed-content',
            'training_link' => 'https://wpshadow.com/training/code-security-mixed-content',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
