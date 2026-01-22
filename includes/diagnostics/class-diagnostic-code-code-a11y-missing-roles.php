<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Missing ARIA Roles
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-missing-roles
 * Training: https://wpshadow.com/training/code-a11y-missing-roles
 */
class Diagnostic_Code_CODE_A11Y_MISSING_ROLES {
    public static function check() {
        return [
            'id' => 'code-a11y-missing-roles',
            'title' => __('Missing ARIA Roles', 'wpshadow'),
            'description' => __('Flags interactive elements missing role attributes.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-missing-roles',
            'training_link' => 'https://wpshadow.com/training/code-a11y-missing-roles',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

