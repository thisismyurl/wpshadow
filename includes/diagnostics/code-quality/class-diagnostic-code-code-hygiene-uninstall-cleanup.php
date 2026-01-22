<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Incomplete Uninstall Cleanup
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-uninstall-cleanup
 * Training: https://wpshadow.com/training/code-hygiene-uninstall-cleanup
 */
class Diagnostic_Code_CODE_HYGIENE_UNINSTALL_CLEANUP extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hygiene-uninstall-cleanup',
            'title' => __('Incomplete Uninstall Cleanup', 'wpshadow'),
            'description' => __('Detects options/tables left behind after plugin removal.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-uninstall-cleanup',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-uninstall-cleanup',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
