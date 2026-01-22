<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Function Name Conflicts
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-function-conflicts
 * Training: https://wpshadow.com/training/code-hygiene-function-conflicts
 */
class Diagnostic_Code_CODE_HYGIENE_FUNCTION_CONFLICTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hygiene-function-conflicts',
            'title' => __('Function Name Conflicts', 'wpshadow'),
            'description' => __('Detects multiple plugins defining same function (no namespace).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-function-conflicts',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-function-conflicts',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
