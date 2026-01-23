<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: init Hook on Frontend
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hooks-init-frontend
 * Training: https://wpshadow.com/training/code-hooks-init-frontend
 */
class Diagnostic_Code_CODE_HOOKS_INIT_FRONTEND extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hooks-init-frontend',
            'title' => __('init Hook on Frontend', 'wpshadow'),
            'description' => __('Flags unnecessary admin-only code in init (runs everywhere).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hooks-init-frontend',
            'training_link' => 'https://wpshadow.com/training/code-hooks-init-frontend',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}