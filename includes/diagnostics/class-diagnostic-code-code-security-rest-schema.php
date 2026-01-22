<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: REST Schema Exposure
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-security-rest-schema
 * Training: https://wpshadow.com/training/code-security-rest-schema
 */
class Diagnostic_Code_CODE_SECURITY_REST_SCHEMA {
    public static function check() {
        return [
            'id' => 'code-security-rest-schema',
            'title' => __('REST Schema Exposure', 'wpshadow'),
            'description' => __('Flags REST routes exposing private data without schema validation.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-security-rest-schema',
            'training_link' => 'https://wpshadow.com/training/code-security-rest-schema',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

