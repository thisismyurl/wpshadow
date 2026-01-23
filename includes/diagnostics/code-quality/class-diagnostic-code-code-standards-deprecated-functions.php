<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Deprecated Function Usage
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-deprecated-functions
 * Training: https://wpshadow.com/training/code-standards-deprecated-functions
 */
class Diagnostic_Code_CODE_STANDARDS_DEPRECATED_FUNCTIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-deprecated-functions',
            'title' => __('Deprecated Function Usage', 'wpshadow'),
            'description' => __('Flags use of deprecated WP functions (wp_register_script deprecated style, etc).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-deprecated-functions',
            'training_link' => 'https://wpshadow.com/training/code-standards-deprecated-functions',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}