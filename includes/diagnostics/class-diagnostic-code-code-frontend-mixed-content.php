<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Mixed HTTP/HTTPS
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-mixed-content
 * Training: https://wpshadow.com/training/code-frontend-mixed-content
 */
class Diagnostic_Code_CODE_FRONTEND_MIXED_CONTENT {
    public static function check() {
        return [
            'id' => 'code-frontend-mixed-content',
            'title' => __('Mixed HTTP/HTTPS', 'wpshadow'),
            'description' => __('Detects resources mixed between secure/insecure protocols.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-mixed-content',
            'training_link' => 'https://wpshadow.com/training/code-frontend-mixed-content',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

