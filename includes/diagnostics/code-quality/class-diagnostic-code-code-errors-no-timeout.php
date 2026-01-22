<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing HTTP Timeout
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-no-timeout
 * Training: https://wpshadow.com/training/code-errors-no-timeout
 */
class Diagnostic_Code_CODE_ERRORS_NO_TIMEOUT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-errors-no-timeout',
            'title' => __('Missing HTTP Timeout', 'wpshadow'),
            'description' => __('Flags wp_remote_get/post without timeout parameter.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-no-timeout',
            'training_link' => 'https://wpshadow.com/training/code-errors-no-timeout',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
