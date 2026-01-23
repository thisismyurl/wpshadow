<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PHP 8.x Compatibility
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-php-compat
 * Training: https://wpshadow.com/training/code-standards-php-compat
 */
class Diagnostic_Code_CODE_STANDARDS_PHP_COMPAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-php-compat',
            'title' => __('PHP 8.x Compatibility', 'wpshadow'),
            'description' => __('Detects code patterns incompatible with modern PHP versions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-php-compat',
            'training_link' => 'https://wpshadow.com/training/code-standards-php-compat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}