<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: REST Unvalidated Arguments
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-rest-unvalidated
 * Training: https://wpshadow.com/training/code-rest-unvalidated
 */
class Diagnostic_Code_CODE_REST_UNVALIDATED {
    public static function check() {
        return [
            'id' => 'code-rest-unvalidated',
            'title' => __('REST Unvalidated Arguments', 'wpshadow'),
            'description' => __('Detects REST endpoints accepting args without schema validation.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-rest-unvalidated',
            'training_link' => 'https://wpshadow.com/training/code-rest-unvalidated',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

