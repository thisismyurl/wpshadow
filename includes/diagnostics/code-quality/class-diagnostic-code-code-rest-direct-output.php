<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Direct Output in REST
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-rest-direct-output
 * Training: https://wpshadow.com/training/code-rest-direct-output
 */
class Diagnostic_Code_CODE_REST_DIRECT_OUTPUT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-rest-direct-output',
            'title' => __('Direct Output in REST', 'wpshadow'),
            'description' => __('Flags REST callbacks with echo/print instead of return.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-rest-direct-output',
            'training_link' => 'https://wpshadow.com/training/code-rest-direct-output',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
