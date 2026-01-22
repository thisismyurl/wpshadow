<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: WP_Error Not Handled
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-errors-wp-error-ignored
 * Training: https://wpshadow.com/training/code-errors-wp-error-ignored
 */
class Diagnostic_Code_CODE_ERRORS_WP_ERROR_IGNORED {
    public static function check() {
        return [
            'id' => 'code-errors-wp-error-ignored',
            'title' => __('WP_Error Not Handled', 'wpshadow'),
            'description' => __('Detects wp_error returns not checked before use.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-errors-wp-error-ignored',
            'training_link' => 'https://wpshadow.com/training/code-errors-wp-error-ignored',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

