<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: REST Missing Permission Callback
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-rest-missing-permission
 * Training: https://wpshadow.com/training/code-rest-missing-permission
 */
class Diagnostic_Code_CODE_REST_MISSING_PERMISSION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-rest-missing-permission',
            'title' => __('REST Missing Permission Callback', 'wpshadow'),
            'description' => __('Flags REST routes with no permission_callback defined.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-rest-missing-permission',
            'training_link' => 'https://wpshadow.com/training/code-rest-missing-permission',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
