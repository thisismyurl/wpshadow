<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Non-PSR Autoloading
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-psr-autoload
 * Training: https://wpshadow.com/training/code-standards-psr-autoload
 */
class Diagnostic_Code_CODE_STANDARDS_PSR_AUTOLOAD extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-psr-autoload',
            'title' => __('Non-PSR Autoloading', 'wpshadow'),
            'description' => __('Flags plugins not following PSR-4 autoloading standards.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-psr-autoload',
            'training_link' => 'https://wpshadow.com/training/code-standards-psr-autoload',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
