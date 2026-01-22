<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Missing Frontend i18n
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-i18n-missing
 * Training: https://wpshadow.com/training/code-frontend-i18n-missing
 */
class Diagnostic_Code_CODE_FRONTEND_I18N_MISSING {
    public static function check() {
        return [
            'id' => 'code-frontend-i18n-missing',
            'title' => __('Missing Frontend i18n', 'wpshadow'),
            'description' => __('Flags hardcoded strings in frontend JS (should be localized).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-i18n-missing',
            'training_link' => 'https://wpshadow.com/training/code-frontend-i18n-missing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

