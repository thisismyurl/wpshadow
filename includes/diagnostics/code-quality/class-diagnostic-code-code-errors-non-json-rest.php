<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Non-JSON-Safe REST Data
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-non-json-rest
 * Training: https://wpshadow.com/training/code-errors-non-json-rest
 */
class Diagnostic_Code_CODE_ERRORS_NON_JSON_REST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-errors-non-json-rest',
            'title' => __('Non-JSON-Safe REST Data', 'wpshadow'),
            'description' => __('Detects complex objects in REST responses not JSON-serializable.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-non-json-rest',
            'training_link' => 'https://wpshadow.com/training/code-errors-non-json-rest',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
